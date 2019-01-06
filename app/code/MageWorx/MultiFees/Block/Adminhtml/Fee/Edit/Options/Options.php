<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Options;

/**
 * Class Options
 */
class Options extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory
     */
    protected $feeOptionCollectionFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Option
     */
    protected $optionResource;

    /**
     * @var string
     */
    protected $_template = 'MageWorx_MultiFees::tab/options.phtml';

    /**
     * Options constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory
     * @param \MageWorx\MultiFees\Model\ResourceModel\Option $optionResource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Option $optionResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry                   = $registry;
        $this->feeOptionCollectionFactory = $feeOptionCollectionFactory;
        $this->optionResource             = $optionResource;
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }

        return $this->_getData('stores');
    }

    /**
     * Returns stores sorted by Sort Order
     *
     * @return array
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort(
                $stores,
                function ($storeA, $storeB) {
                    if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                        return $storeA->getId() < $storeB->getId() ? -1 : 1;
                    }

                    return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
                }
            );
        }

        return $stores;
    }

    /**
     * Retrieve attribute option values if attribute input type select or multiselect
     *
     * @return array
     */
    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = [];

            /** @var \MageWorx\MultiFees\Model\AbstractFee $fee * */
            $fee = $this->getFeeObject();

            $optionCollection = $this->_getOptionValuesCollection($fee);
            if ($optionCollection) {
                $values = $this->_prepareOptionValues($optionCollection);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }

    /**
     * @return mixed
     */
    public function getPriceTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            [
                'id'    => $this->getFieldId() . '_<%- data.id %>_price_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[price_type][<%- data.id %>]'
        )->setOptions(
            [
                ['value' => 'fixed', 'label' => 'Fixed'],
                ['value' => 'percent', 'label' => 'Percent']
            ]
        );

        return $select->getHtml();
    }

    /**
     * @return string
     */
    protected function getFieldId()
    {
        return 'option';
    }

    /**
     * @return string
     */
    protected function getFieldName()
    {
        return $this->getFieldId();
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $optionCollection
     * @return array
     */
    protected function _prepareOptionValues(
        $optionCollection
    ) {
        $values = [];
        foreach ($optionCollection as $option) {
            $bunch = $this->_prepareFeeOptionValues($option);
            foreach ($bunch as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * Retrieve option values collection
     *
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @return \MageWorx\MultiFees\Model\ResourceModel\Option\Collection
     */
    protected function _getOptionValuesCollection(\MageWorx\MultiFees\Model\AbstractFee $fee)
    {
        return $this->feeOptionCollectionFactory->create()->addFeeFilter($fee->getId())->load();
    }

    /**
     * Prepare option values of user defined attribute
     *
     * @param array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option $option
     * @param array $defaultValues
     * @return array
     */
    protected function _prepareFeeOptionValues($option)
    {
        $optionId = $option->getFeeOptionId();

        $value = $option->getData();

        $value['checked']    = $value['is_default'] ? 'checked="checked"' : '';
        $value['intype']     = $this->getInType();
        $value['id']         = $optionId;
        $value['sort_order'] = $option->getSortOrder();

        foreach ($this->getStores() as $store) {
            $storeId                   = $store->getId();
            $storeValues               = $this->getStoreOptionValues($storeId);
            $value['store' . $storeId] = '';
            if (isset($storeValues[$optionId])) {
                $value['store' . $storeId] = $storeValues[$optionId];
            }
        }

        return [$value];
    }

    /**
     * Retrieve fee option values for given store id
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_' . $storeId);
        if ($values === null) {
            $values           = [];
            $valuesCollection = $this->feeOptionCollectionFactory->create()->addFeeFilter(
                $this->getFeeObject()->getId()
            )->addStoreLanguage(
                $storeId,
                false
            )->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getFeeOptionId()] = $item->getTitle();
            }
            $this->setData('store_option_values_' . $storeId, $values);
        }

        return $values;
    }

    /**
     * Retrieve fee object from registry
     *
     * @return \MageWorx\MultiFees\Model\ResourceModel\FeeAbstractResource
     */
    protected function getFeeObject()
    {
        return $this->registry->registry('mageworx_multifees_fee');
    }

    /**
     * Retrieve input type for "is_default" option value property
     *
     * @return string
     */
    protected function getInType()
    {
        return 'checkbox';
    }

    /**
     * @return null
     */
    public function getReadOnly()
    {
        return null;
    }
}
