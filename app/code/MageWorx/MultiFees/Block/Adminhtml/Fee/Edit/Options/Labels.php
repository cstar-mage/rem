<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Adminhtml\Fee\Edit\Options;

/**
 * Fee add/edit form labels tab
 */
class Labels extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $_template = 'MageWorx_MultiFees::tab/labels.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores());
        }

        return $this->_getData('stores');
    }

    /**
     * Retrieve titles of fee for each store
     *
     * @return array
     */
    public function getFeeNamesValues()
    {
        $fee         = $this->getFeeObject();
        $storeLabels = $fee->getStoreNames();

        $values = [];

        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }

        return $values;
    }

    /**
     * Retrieve titles of fee for each store
     *
     * @return array
     */
    public function getFeeDescriptionValues()
    {
        $fee         = $this->getFeeObject();
        $storeLabels = $fee->getStoreDescriptions();

        $values = [];

        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    public function getCustomerMessageTitleValues()
    {
        $fee         = $this->getFeeObject();
        $storeLabels = $fee->getStoreCustomerMessageTitles();

        $values = [];

        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    public function getDateTitleValues()
    {
        $fee         = $this->getFeeObject();
        $storeLabels = $fee->getStoreDateFieldTitles();

        $values = [];

        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }

        return $values;
    }

    /**
     * Retrieve fee object from registry
     *
     * @return \MageWorx\MultiFees\Model\AbstractFee
     */
    private function getFeeObject()
    {
        return $this->registry->registry('mageworx_multifees_fee');
    }
}
