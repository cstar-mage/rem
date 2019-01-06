<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Eav\Model\Config as EavConfig;

/**
 * Class CustomExclusionStrategy
 * @package Amasty\Shopby\Plugin\CatalogSearch\Model\Search\FilterMapper
 */
class CustomExclusionStrategy
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * List of fields that can be processed by exclusion strategy
     * @var array
     */
    private $validFields = ['am_on_sale', 'am_is_new', 'rating_summary'];

    /**
     * @var string
     */
    private $productIdLink;

    /**
     * CustomExclusionStrategy constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param ConditionManager $conditionManager
     * @param EavConfig $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        ConditionManager $conditionManager,
        EavConfig $eavConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->localeDate = $localeDate;
        $this->conditionManager = $conditionManager;
        $this->eavConfig = $eavConfig;
        $this->productIdLink = $productMetadata->getEdition() == 'Enterprise' ? 'row_id' : 'entity_id';
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        if (!in_array($filter->getField(), $this->validFields, true)) {
            return false;
        }

        switch ($filter->getField()) {
            case 'am_on_sale':
                $isApplied = $this->applyOnSaleFilter($select);
                break;
            case 'am_is_new':
                $isApplied = $this->applyIsNewFilter($select);
                break;
            case 'rating_summary':
                $isApplied = $this->applyRatingFilter($filter, $select);
                break;
            default:
                $isApplied = false;
        }

        return $isApplied;
    }

    /**
     * Applies on_sale filter
     *
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyOnSaleFilter(\Magento\Framework\DB\Select $select)
    {
        $tableName = $this->resourceConnection->getTableName('catalog_product_index_price');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        $conditions = [
            "catalog_rule.product_id = {$mainTableAlias}.entity_id",
            '(catalog_rule.latest_start_date < NOW() OR catalog_rule.latest_start_date IS NULL)',
            '(catalog_rule.earliest_end_date > NOW() OR catalog_rule.earliest_end_date IS NULL)',
            "catalog_rule.website_id = '{$websiteId}'",
            "catalog_rule.customer_group_id = '{$customerGroupId}'"
        ];
        $select->joinLeft(
            ['catalog_rule' => $this->resourceConnection->getTableName('catalogrule_product_price')],
            implode(' AND ', $conditions),
            null
        );

        $select->joinLeft(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            "relation.child_id = {$mainTableAlias}.entity_id",
            ['parent_id' => 'relation.parent_id']
        );

        $priceIndexConditions = [
            "{$mainTableAlias}.entity_id = on_sale_price_index.entity_id",
            "on_sale_price_index.website_id = {$this->storeManager->getWebsite()->getId()}"
        ];
        $select->joinInner(['on_sale_price_index' => $tableName], implode(" AND ", $priceIndexConditions), []);
        $select->where('ifnull(catalog_rule.rule_price, on_sale_price_index.final_price) < on_sale_price_index.price');

        return true;
    }

    /**
     * Applies is_new filter
     *
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyIsNewFilter(\Magento\Framework\DB\Select $select)
    {
        $mainTableAlias = $this->extractTableAliasFromSelect($select);
        $todayStartOfDayDate = $this->localeDate->date()
            ->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->localeDate->date()
            ->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $mainTable = $this->resourceConnection->getTableName('catalog_product_entity_datetime');

        $joinConditions = [
            "news_from_date_attribute.attribute_id = {$this->getAttributeId('news_from_date')}",
            sprintf('%s.entity_id = news_from_date_attribute.%s', $mainTableAlias, $this->productIdLink),
            "(news_from_date_attribute.value <= '{$todayEndOfDayDate}' OR news_from_date_attribute.value IS NULL)",
        ];
        $select->joinLeft(
            ['news_from_date_attribute' => $mainTable],
            implode(' AND ', $joinConditions),
            []
        );

        $joinConditions = [
            "news_to_date_attribute.attribute_id = {$this->getAttributeId('news_to_date')}",
            sprintf('%s.entity_id = news_to_date_attribute.%s', $mainTableAlias, $this->productIdLink),
            "(news_to_date_attribute.value >= '{$todayStartOfDayDate}' OR news_to_date_attribute.value IS NULL)",
        ];
        $select->joinLeft(
            ['news_to_date_attribute' => $mainTable],
            implode(' AND ', $joinConditions),
            []
        );

        $whereConditions = [
            'news_from_date_attribute.value IS NOT NULL',
            'news_to_date_attribute.value IS NOT NULL'
        ];
        $select->where(implode(' OR ', $whereConditions));

        return true;
    }

    /**
     * Applies rating_summary filter
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     */
    private function applyRatingFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $filter->getField() . RequestGenerator::FILTER_SUFFIX;
        $select->joinLeft(
            [$alias => $this->resourceConnection->getTableName('review_entity_summary')],
            sprintf(
                '`rating_summary_filter`.`entity_pk_value`=`search_index`.entity_id
                AND `rating_summary_filter`.entity_type = 1
                AND `rating_summary_filter`.store_id  =  %d',
                $this->storeManager->getStore()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * Returns visibility attribute id
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeId($attributeCode)
    {
        $attr = $this->eavConfig->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode
        );

        return (int) $attr->getId();
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return string|null
     */
    private function extractTableAliasFromSelect(\Magento\Framework\DB\Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(\Magento\Framework\DB\Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === \Magento\Framework\DB\Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
