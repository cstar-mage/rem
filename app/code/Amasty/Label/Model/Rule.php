<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    protected $_product;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $amastySerializer;

    protected function _construct()
    {
        $this->amastySerializer = $this->getData('amastySerializer');
        if (!$this->amastySerializer) {
            $this->amastySerializer = $this->serializer;
        }
        parent::_construct();
        $this->_init('Amasty\Label\Model\ResourceModel\Labels');
        $this->setIdFieldName('entity_id');
    }

    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_productsFilter = $product->getId();
    }

    public function getLabelMatchingProductIds() //skip aftergetLabelMatchingProductIds plugin
    {
        if ($this->_productIds === null) {

            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->_productFactory->create()
                ]
            );

        }

        return $this->_productIds;
    }

    public function callbackValidateProduct($args)
    {
        $product = $args['product'];
        $product->setData($args['row']);

        $stores = $this->getStores();
        $stores = explode(',', $stores);
        $results = [];

        foreach ($stores as $storeId) {
            $product->setStoreId($storeId);
            $validate = $this->getConditions()->validate($product);
            if ($validate) {
                $results[$storeId] = $validate;
                $this->_productIds[$product->getId()] = $results;
            }
        }
    }

    /**
     * fix fatal error after migration from 2.1 to 2.2 magento
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                /* change serializer*/
                $conditions = $this->amastySerializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }
}
