<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class Shipmentsms implements ObserverInterface {

    protected $_collection;

    public function __construct(\Psr\Log\LoggerInterface $logger, \Magento\Sales\Model\ResourceModel\Order\Collection $collection) {
        $this->_collection = $collection;
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $model = $objectManager->create('Emipro\Smsnotification\Model\Smsevent')->load(3);


        if($model->getIsActive() == 1)
        {
            $shipment = $observer->getEvent()->getShipment();
            $order_id = $shipment->getOrderId();

            $shipmentCollection = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')->addFieldToFilter('order_id',array('eq' => $order_id));
            foreach ($shipmentCollection as $value) {
                $incrementId = $value['increment_id'];
            }
            /*
            get store id using order id
             */
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
            $storeId = $order->getStoreId();

            $this->_logger->addDebug("shipment order id ".$order_id);
            $helper_factory = $objectManager->get('\Emipro\Smsnotification\Helper\Data');
            $smsText = $helper_factory->processTextForShipment("sales_order_shipment_save_after", $order_id,$messageType=2, $incrementId, $storeId);
        }
    }

}
