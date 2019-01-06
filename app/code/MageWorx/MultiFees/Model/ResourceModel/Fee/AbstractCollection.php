<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel\Fee;

use MageWorx\MultiFees\Model\AbstractFee;
use MageWorx\MultiFees\Model\Option;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;

abstract class AbstractCollection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection implements SearchResultInterface
{
    const FEE_MODEL_CLASS_NAME          = '';
    const FEE_RESOURCE_MODEL_CLASS_NAME = '';

    /**
     * Options for the count method
     *
     * @see \MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection::count()
     */
    const COUNT_ALL               = 0;
    const COUNT_EXCEPT_HIDDEN_FEE = 1;
    const COUNT_HIDDEN_FEE        = 2;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $_idFieldName = 'fee_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Flag: is fee labels was already joined to the collection
     *
     * @var bool
     */
    protected $feeLabelsJoined = false;

    protected $_associatedEntitiesMap = [
        'store'          => [
            'associations_table' => 'mageworx_multifees_fee_store',
            'rule_id_field'      => 'fee_id',
            'entity_id_field'    => 'store_id',
        ],
        'customer_group' => [
            'associations_table' => 'mageworx_multifees_fee_customer_group',
            'rule_id_field'      => 'fee_id',
            'entity_id_field'    => 'customer_group_id',
        ],
    ];

    /**
     * Aggregations
     *
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    protected $aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(static::FEE_MODEL_CLASS_NAME, static::FEE_RESOURCE_MODEL_CLASS_NAME);
        $this->_map['fields']['fee_id'] = 'main_table.fee_id';
        $this->_map['fields']['store']  = 'store_table.store_id';
    }

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
        $this->addFeeTypeFilter();
        $this->joinStoreRelationTable('mageworx_multifees_fee_store', 'fee_id');
        $this->addLabels(0);
    }

    /**
     * Always filter fee collection by its own type
     *
     * @return $this
     */
    abstract protected function addFeeTypeFilter();

    /**
     * Filter collection by specified store, customer group, date.
     * Filter collection to use only active rules.
     * Involved sorting by sort_order column.
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setValidationFilter($storeId, $customerGroupId)
    {
        if (!$this->getFlag('validation_filter')) {
            $this->addStoreGroupFilter($storeId, $customerGroupId);
            $this->setOrder('sort_order', self::SORT_ORDER_DESC);
            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * Filter collection by store(s), customer group(s).
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addStoreGroupFilter($storeId, $customerGroupId)
    {
        if (!$this->getFlag('store_group_filter') && !$this->getFlag('validation_filter')) {
            $this->addStoreRuleFilter($storeId);
            $this->addCustomerGroupFilter($customerGroupId);
            $this->setFlag('store_group_filter', true);
        }

        return $this;
    }

    /**
     * Customer group filter
     *
     * @param $customerGroupId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
        $connection = $this->getConnection();
        $this->getSelect()->joinInner(
            ['customer_group_ids' => $this->getTable($entityInfo['associations_table'])],
            $connection->quoteInto(
                'main_table.' .
                $entityInfo['rule_id_field'] .
                ' = customer_group_ids.' .
                $entityInfo['rule_id_field'] .
                ' AND customer_group_ids.' .
                $entityInfo['entity_id_field'] .
                ' = ?',
                (int)$customerGroupId
            ),
            []
        );

        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }

        return $this;
    }

    /**
     * Limit rules collection by specific stores
     *
     * @param int|int[]|\Magento\Store\Model\Store $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addStoreRuleFilter($storeId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('store');
        if (!$this->getFlag('is_store_table_joined')) {
            $this->setFlag('is_store_table_joined', true);
            if ($storeId instanceof \Magento\Store\Model\Store) {
                $storeId = $storeId->getId();
            }
            $this->getSelect()->joinLeft(
                ['store' => $this->getTable($entityInfo['associations_table'])],
                'main_table.' . $entityInfo['rule_id_field'] . ' = store.' . $entityInfo['rule_id_field'],
                []
            );
        }

        parent::addFieldToFilter(
            'store.store_id',
            [
                ['eq' => $storeId],
                ['eq' => '0'],
            ]
        );

        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);

        if (count($items)) {
            $connection = $this->getConnection();
            $select     = $connection->select()->from(['fee_entity_store' => $this->getTable($tableName)])
                                     ->where('fee_entity_store.' . $columnName . ' IN (?)', $items);
            $result     = $connection->fetchPairs($select);

            if ($result) {
                foreach ($this as $item) {
                    $entityId = $item->getData($columnName);
                    if (!isset($result[$entityId])) {
                        continue;
                    }
                    if ($result[$entityId] == 0) {
                        $stores    = $this->storeManager->getStores(false, true);
                        $storeId   = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId   = $result[$item->getData($columnName)];
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', [$result[$entityId]]);
                }
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;

        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     * @throws \Exception
     */
    protected function _afterLoad()
    {
        /**
         * @todo admin grid check before delete
         */
        $this->performAfterLoad('mageworx_multifees_fee_store', 'fee_id');

        $this->setFlag('add_websites_to_result', false);

        return parent::_afterLoad();
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $this->setFlag('store_filter_added', true);
        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * @param null|int $storeId
     * @return $this
     */
    public function addLabels($storeId = null)
    {
        if ($this->feeLabelsJoined) {
            return $this;
        }

        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if ($storeId != 0) {
            $this->getSelect()
                 ->joinLeft(
                     ['language_fee_default' => $this->getTable('mageworx_multifees_fee_language')],
                     'main_table.fee_id = language_fee_default.fee_id AND language_fee_default.store_id = 0'
                 )
                 ->joinLeft(
                     ['language_fee' => $this->getTable('mageworx_multifees_fee_language')],
                     'main_table.fee_id = language_fee.fee_id AND language_fee.store_id = ' . $storeId,
                     [
                         'title'                  => "IF(language_fee.title!='', language_fee.title, language_fee_default.title)",
                         'description'            => "IF(language_fee.description!='', language_fee.description, language_fee_default.description)",
                         'customer_message_title' => "IF(language_fee.customer_message_title!='', language_fee.customer_message_title, language_fee_default.customer_message_title)",
                         'date_field_title'       => "IF(language_fee.date_field_title!='', language_fee.date_field_title, language_fee_default.date_field_title)",
                     ]
                 );
        } else {
            $this->getSelect()->joinLeft(
                ['language_fee_default' => $this->getTable('mageworx_multifees_fee_language')],
                'main_table.fee_id = language_fee_default.fee_id AND language_fee_default.store_id = 0',
                ['title', 'description', 'customer_message_title', 'date_field_title']
            );
        }

        $this->feeLabelsJoined = true;

        return $this;
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param bool $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = true)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter(
                'status',
                (int)$isActive ? AbstractFee::STATUS_ENABLED : AbstractFee::STATUS_DISABLED
            );
            $this->setFlag('is_active_filter', true);
        }

        return $this;
    }

    /**
     * @param array $types
     * @return $this
     */
    public function addTypeFilter(array $types = [])
    {
        if ($types) {
            $this->getSelect()->where('main_table.type IN (?)', $types);
        }

        return $this;
    }

    /**
     * @param bool $isRequired
     * @return $this
     */
    public function addRequiredFilter($isRequired = true)
    {
        if ($isRequired) {
            $this->getSelect()->where('main_table.required = ?', 1);
        }

        return $this;
    }

    /**
     * @param bool $isDefault
     * @return $this
     */
    public function addIsDefaultFilter($isDefault = true)
    {
        if ($isDefault) {
            $this->getSelect()->join(
                ['option_table' => $this->getTable('mageworx_multifees_fee_option')],
                'main_table.fee_id = option_table.fee_id',
                []
            )
                 ->where('option_table.is_default = ?', Option::VALUE_IS_DEFAULT);
            $this->getSelect()->distinct();
        }

        return $this;
    }

    /**
     * Add sort order by ascending
     *
     * @return $this
     */
    public function addSortOrder()
    {
        $this->getSelect()->order('main_table.sort_order', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Retrieve count of collection loaded items.
     * In case an incorrect option is specified returns count of all items in the collection
     *
     * @param int $option
     * @return int
     */
    public function count($option = self::COUNT_ALL)
    {
        $this->load();

        switch ($option) {
            case self::COUNT_EXCEPT_HIDDEN_FEE:
                $count = 0;
                // Remove hidden fees from count
                /** @var \MageWorx\MultiFees\Model\AbstractFee $item */
                foreach ($this->_items as $item) {
                    if ($item->getInputType() != FeeInterface::FEE_INPUT_TYPE_HIDDEN) {
                        $count++;
                    }
                }
                break;
            case self::COUNT_HIDDEN_FEE:
                $count = 0;
                // Count only hidden fees
                /** @var \MageWorx\MultiFees\Model\AbstractFee $item */
                foreach ($this->_items as $item) {
                    if ($item->getInputType() == FeeInterface::FEE_INPUT_TYPE_HIDDEN) {
                        $count++;
                    }
                }
                break;
            case self::COUNT_ALL:
            default:
                $count = count($this->_items);
                break;
        }

        return $count;
    }
}
