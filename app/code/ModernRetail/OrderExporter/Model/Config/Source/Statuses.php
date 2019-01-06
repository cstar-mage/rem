<?php

namespace ModernRetail\OrderExporter\Model\Config\Source;

class Statuses implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $statusCollectionFactory = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory');
        $options = $statusCollectionFactory->create()->toOptionArray();
        return $options;
    }
}