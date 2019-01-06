<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class Ordersuccess implements ObserverInterface {

    protected $_collection;

    public function __construct(\Psr\Log\LoggerInterface $logger, \Magento\Sales\Model\ResourceModel\Order\Collection $collection) {
        $this->_collection = $collection;
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Emipro\Smsnotification\Model\Smsevent')->load(1);
        if($model->getIsActive() == 1)
        {
            $checkout_session=$objectManager->get("Magento\Checkout\Model\Session");
            $order_data=$checkout_session->getLastRealOrder();
            $order_id = $order_data->getId();
            $this->_logger->addDebug("sudcess order id ".$order_id);
            /*
            get store id using order id
             */
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
            $storeId = $order->getStoreId();

            $helper_factory = $objectManager->get('\Emipro\Smsnotification\Helper\Data');
            $smsText = $helper_factory->processText("checkout_onepage_controller_success_action", $order_id,$messageType=2, $storeId);
        }
    }
}
