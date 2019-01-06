<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model;

use Amasty\Base\Model\Serializer;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class AbstractLabels extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Label cache tag
     */
    const CACHE_TAG = 'amasty_label';
    public $_cacheTag = 'amasty_label';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogData = null;

    /**
     * Stock Registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Amasty\Label\Helper\Config
     */
    protected $helper;

    /**
     * @var  array
     */
    protected $prices;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Amasty\Label\Model\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Amasty\Label\Helper\Config $helper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->catalogData = $catalogData;
        $this->stockRegistry = $stockRegistry;
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->date = $date;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->setCacheTags([self::CACHE_TAG]);
        $this->setCacheTags([self::CACHE_TAG]);
    }

    public function init(\Magento\Catalog\Model\Product $product, $mode = null, $parent = null)
    {
        $this->setProduct($product);
        $this->setParentProduct($parent);
        $this->prices = [];

        // auto detect product page
        if ($mode) {
            $this->setMode($mode == 'category' ? 'cat' : 'prod');
        } else {
            $this->setMode('cat');
        }
    }

    public function checkDateRange()
    {
        $now = $this->timezone->date()->format('Y-m-d H:i:s');
        if ($this->getDateRangeEnabled()) {
            $fromDate = $this->getFromDate() ? $this->getFromDate() : null;
            $toDate = $this->getToDate() ? $this->getToDate() : null;

            if (($fromDate !== null && $now < $fromDate)
                || ($toDate !== null && $now > $toDate)
            ) {
                return false;
            }
        }

        return true;
    }

    public function getLabelMatchingProductIds()
    {
        if ("" !== $this->getData('cond_serialize')) {
            /** @var \Amasty\Label\Model\Rule $ruleModel */
            $ruleModel = $this->ruleFactory->create();
            $ruleModel->setConditions([]);
            $ruleModel->setStores($this->getData('stores'));
            $ruleModel->setConditionsSerialized($this->getData('cond_serialize'));
            if ($this->getProduct()) {
                $ruleModel->setProduct($this->getProduct());
            }

            return $ruleModel->getLabelMatchingProductIds();
        }

        return null;
    }

    public function isApplicable()
    {
        if (!$this->getProduct()) {
            return false;
        }

        return $this->isApplicableForConditions() && $this->isApplicableForCustomRules();
    }

    public function isApplicableForConditions()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getProduct();

        if ("" != $this->getData('cond_serialize')) {
            $productIds = $this->getLabelMatchingProductIds();

            $inArray = array_key_exists($product->getId(), $productIds)
                && array_key_exists($product->getStore()->getId(), $productIds[$product->getId()]);

            if (!$inArray) {
                return false;
            }
        }

        return true;
    }

    public function isApplicableForCustomRules()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getProduct();
        if ($this->getPriceRangeEnabled()) {
            $result = $this->_getPriceCondition($product);
            if (!$result) {
                return false;
            }
        }

        $stockRangeEnabled = $this->getProductStockEnabled();
        if ($stockRangeEnabled == "1") {
            $qty = $this->_getProductQty($product);
            $lessThan = $this->getStockLess();
            $higherThan = $this->getStockHigher();
            if ($lessThan !== null && $lessThan >= 0 && $lessThan <= $qty) {
                return false;
            }

            if ($higherThan !== null && $higherThan >= 0 && $higherThan >= $qty) {
                return false;
            }
        }

        $stockStatus = $this->getStockStatus();
        if ($stockStatus) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            $inStock = $stockItem->getIsInStock() && $product->isAvailable() ? 2 : 1;
            if ($inStock != $stockStatus) {
                return false;
            }
        }

        if ($this->getIsNew()) {
            $isNew = $this->_isNew($product) ? 2 : 1;
            if ($this->getIsNew() != $isNew) {
                return false;
            }
        }

        if ($this->getIsSale()) {
            $isSale = $this->_isSale() ? 2 : 1;
            if ($this->getIsSale() != $isSale) {
                return false;
            }
        }

        return true;
    }

    protected function _getPriceCondition($product)
    {
        switch ($this->getByPrice()) {
            case '0': // Base Price
                $price = $this->catalogData->getTaxPrice($product, $product->getData('price'), false);
                break;
            case '1': // Special Price
                $price = $product->getSpecialPrice();
                break;
            case '2': // Final Price
                $price = $this->catalogData->getTaxPrice($product, $product->getFinalPrice(), false);
                break;
            case '3': // Final Price Incl Tax
                $price = $this->catalogData->getTaxPrice($product, $product->getFinalPrice(), true);
                break;
            case '4': // Starting from Price
                $price = $this->_getMinimalPrice($product);
                break;
            case '5': // Starting to Price
                $price = $this->_getMaximalPrice($product);
                break;
        }
        if ($product->getTypeId() == 'bundle') {
            $minimalPrice = $this->catalogData->getTaxPrice($product, $product->getData('min_price'), true);
            $maximalPrice = $this->catalogData->getTaxPrice($product, $product->getData('max_price'), true);
            if ($minimalPrice < $this->getFromPrice() && $maximalPrice > $this->getToPrice()) {
                return false;
            }
        } elseif ($price < $this->getFromPrice() || $price > $this->getToPrice()) {
            return false;
        }

        return true;
    }

    protected function _getMinimalPrice($product)
    {
        $minimalPrice = $this->catalogData->getTaxPrice($product, $product->getMinimalPrice(), true);

        if ($product->getTypeId() == 'grouped') {
            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($associatedProducts as $item) {
                $temp = $this->catalogData->getTaxPrice($item, $item->getFinalPrice(), true);
                if ($minimalPrice === null || $temp < $minimalPrice) {
                    $minimalPrice = $temp;
                }
            }
        }

        return $minimalPrice;
    }

    protected function _getMaximalPrice($product)
    {
        $maximalPrice = 0;
        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            $associatedProducts = $this->helper->getUsedProducts($product);
            foreach ($associatedProducts as $item) {
                $qty = $item->getQty() * 1 ? $item->getQty() * 1 : 1;
                $maximalPrice += $qty * $this->catalogData->getTaxPrice($item, $item->getFinalPrice(), true);
            }
        }
        if (!$maximalPrice) {
            $maximalPrice = $this->catalogData->getTaxPrice($product, $product->getFinalPrice(), true);
        }

        return $maximalPrice;
    }

    protected function _getProductQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $quantity = $stockItem->getQty();

        return (int)$quantity;
    }

    protected function _isNew(\Magento\Catalog\Model\Product $p)
    {
        $fromDate = '';
        $toDate = '';
        if ($this->helper->getModuleConfig('new/is_new')) {
            $fromDate = $p->getNewsFromDate();
            $toDate = $p->getNewsToDate();
        }

        if (!$fromDate && !$toDate) {
            if ($this->helper->getModuleConfig('new/creation_date')) {
                $days = $this->helper->getModuleConfig('new/days');
                if (!$days) {
                    return false;
                }
                $createdAt = strtotime($p->getCreatedAt());
                $now = $this->timezone->date()->format('U');
                return ($now - $createdAt <= $days * 86400); // 60 sec. * 60 min. * 24 hours = 86400 sec.
            } else {
                return false;
            }
        }

        $now = $this->timezone->date()->format('Y-m-d H:i:s');
        if ($fromDate && $now < $fromDate) {
            return false;
        }

        if ($toDate) {
            $toDate = str_replace('00:00:00', '23:59:59', $toDate);
            if ($now > $toDate) {
                return false;
            }
        }

        return true;
    }

    protected function _isSale()
    {
        $price = $this->_loadPrices();
        if ($price['price'] <= 0 || !$price['special_price']) {
            return false;
        }

        // in dollars
        $diff = $price['price'] - $price['special_price'];
        $min = $this->helper->getModuleConfig('on_sale/sale_min');
        if ($diff < 0.001 || ($min && $diff < $min)) {
            return false;
        }

        // in percents
        $value = ceil($diff * 100 / $price['price']);
        $minPercent = $this->helper->getModuleConfig('on_sale/sale_min_percent');
        if ($minPercent && $value < $minPercent) {
            return false;
        }

        return true;
    }

    protected function _loadPrices()
    {
        if (!$this->prices) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct();
            /** @var \Magento\Catalog\Model\Product $parent */
            $parent = $this->getParentProduct();

            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
	            $regularPrice = $this->priceCurrency->convertAndRound($regularPrice);
            }

            $specialPrice = 0;
            if ($this->getIsSale()
                && $this->getSpecialPriceOnly()
            ) {
                $now = $this->date->date('Y-m-d 00:00:00');
                if ($product->getSpecialFromDate()
                    && $now >= $product->getSpecialFromDate()
                ) {
                    $specialPrice = $product->getPriceInfo()->getPrice('special_price')->getValue();
                    if ($product->getSpecialToDate()
                        && $now > $product->getSpecialToDate()
                    ) {
                        $specialPrice = 0;
                    }
                }
            } else {
                $specialPrice = $product->getPriceModel()->getFinalPrice(null, $product);

                if ($product->getTypeId() == 'bundle') {
                    $regularPrice = $product->getPriceModel()->getTotalPrices($product, 'min');

                    $price = $product->getPriceInfo()->getPrice('special_price')->getValue();
                    if ($price !== null && $price < 100) {
                        $specialPrice = ($regularPrice / 100) * $price;
                    }
                }
            }

            if ($parent && ($parent->getTypeId() == Grouped::TYPE_CODE)) {
                $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($usedProds as $child) {
                    if ($child->getId() != $product->getId()) {
                        $regularPrice += $child->getPrice();
                        $specialPrice += $child->getFinalPrice();
                    }
                }
            }
            $this->prices = [
                'price' => $regularPrice,
                'special_price' => $specialPrice
            ];
        }

        return $this->prices;
    }

    /**
     * Get value by label mode
     * @return string
     */
    public function getValue($key)
    {
        $data = $this->getData($this->getMode() . '_' . $key);

        return $data;
    }

    public function getCacheTags()
    {
        $tags = false;
        if ($this->_cacheTag) {
            if ($this->_cacheTag === true) {
                $tags = [];
            } else {
                if (is_array($this->_cacheTag)) {
                    $tags = $this->_cacheTag;
                } else {
                    $tags = [$this->_cacheTag];
                }

                $idTags = $this->getCacheIdTags();
                if ($idTags) {
                    $tags = array_merge($tags, $idTags);
                }
            }
        }
        return $tags;
    }

    /**
     * Get cahce tags associated with object id
     *
     * @return array|bool
     */
    public function getCacheIdTags()
    {
        $tags = false;
        if ($this->getId() && $this->_cacheTag) {
            $tags = [];
            if (is_array($this->_cacheTag)) {
                foreach ($this->_cacheTag as $_tag) {
                    $tags[] = $_tag . '_' . $this->getId();
                }
            } else {
                $tags[] = $this->_cacheTag . '_' . $this->getId();
            }
        }
        return $tags;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Label\Model\ResourceModel\Labels');
        $this->setIdFieldName('label_id');
    }

}
