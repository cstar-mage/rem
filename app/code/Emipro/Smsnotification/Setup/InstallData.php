<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $stores = $objectManager->get('Magento\Store\Model\Store');
        $store_id = array();
        foreach ($stores->getCollection() as $store) {
                array_push($store_id, $store->getStoreId());
        }


        //main table data insert start
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_success_main = $objectManager->create("Emipro\Smsnotification\Model\Smsevent");
        $order_success_main->setSmsTitle("Order Confirmation");
        $order_success_main->setSmsEvents("checkout_onepage_controller_success_action");
        $order_success_main->setSmsContent("Order Saved. Order no:- {{var order_no}} Product name:- {{var product_name}}");
        $order_success_main->setIsActive(1);
        $order_success_main->save();

        $invoice_create_main = $objectManager->create("Emipro\Smsnotification\Model\Smsevent");
        $invoice_create_main->setSmsTitle("Invoice Creation");
        $invoice_create_main->setSmsEvents("sales_order_invoice_pay");
        $invoice_create_main->setSmsContent("Invoice Sent. Order no:- {{var order_no}} Payment Amount:- {{var payment_amount}}");
        $invoice_create_main->setIsActive(1);
        $invoice_create_main->save();

        $shipment_main = $objectManager->create("Emipro\Smsnotification\Model\Smsevent");
        $shipment_main->setSmsTitle("Order Shipment");
        $shipment_main->setSmsEvents("sales_order_shipment_save_after");
        $shipment_main->setSmsContent("Order Shipped Successfully.Order no:- {{var order_no}} Shipping Method:- {{var shipping_method}}");
        $shipment_main->setIsActive(1);
        $shipment_main->save();

        $credit_memo_main = $objectManager->create("Emipro\Smsnotification\Model\Smsevent");
        $credit_memo_main->setSmsTitle("Credit Memo Creation");
        $credit_memo_main->setSmsEvents("sales_order_creditmemo_save_after");
        $credit_memo_main->setSmsContent("Credit Memo generated. Order no:- {{var order_no}} Payment Amount:- {{var payment_amount}}");
        $credit_memo_main->setIsActive(1);
        $credit_memo_main->save();

        $order_cancel_main = $objectManager->create("Emipro\Smsnotification\Model\Smsevent");
        $order_cancel_main->setSmsTitle("Order Cancelled");
        $order_cancel_main->setSmsEvents("order_cancel_after");
        $order_cancel_main->setSmsContent("Order Cancelled Successfully. Order no:- {{var order_no}}");
        $order_cancel_main->setIsActive(1);
        $order_cancel_main->save();

        // main table data data insert end 
        //insert data for store views
        if (count($store_id > 0)) {
            foreach ($store_id as $key => $value) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order_success = $objectManager->create("Emipro\Smsnotification\Model\Smsstore");
                $order_success->setSmsTitle("Order Confirmation");
                $order_success->setSmsEvents("checkout_onepage_controller_success_action");
                $order_success->setSmsContent("Order Saved. Order no:- {{var order_no}} Product name:- {{var product_name}}");
                $order_success->setIsActive(1);
                $order_success->setStoreId($value);
                $order_success->setUseDefault(1);
                $order_success->setMainEntityId($order_success_main->getId());
                $order_success->save();

                $invoice_create = $objectManager->create("Emipro\Smsnotification\Model\Smsstore");
                $invoice_create->setSmsTitle("Invoice Creation");
                $invoice_create->setSmsEvents("sales_order_invoice_pay");
                $invoice_create->setSmsContent("Invoice Sent. Order no:- {{var order_no}} Payment Amount:- {{var payment_amount}}");
                $invoice_create->setIsActive(1);
                $invoice_create->setStoreId($value);
                $invoice_create->setUseDefault(1);
                $invoice_create->setMainEntityId($invoice_create_main->getId());
                $invoice_create->save();

                $shipment = $objectManager->create("Emipro\Smsnotification\Model\Smsstore");
                $shipment->setSmsTitle("Order Shipment");
                $shipment->setSmsEvents("sales_order_shipment_save_after");
                $shipment->setSmsContent("Order Shipped Successfully.Order no:- {{var order_no}} Shipping Method:- {{var shipping_method}}");
                $shipment->setIsActive(1);
                $shipment->setStoreId($value);
                $shipment->setUseDefault(1);
                $shipment->setMainEntityId($shipment_main->getId());
                $shipment->save();

                $credit_memo = $objectManager->create("Emipro\Smsnotification\Model\Smsstore");
                $credit_memo->setSmsTitle("Credit Memo Creation");
                $credit_memo->setSmsEvents("sales_order_creditmemo_save_after");
                $credit_memo->setSmsContent("Credit Memo generated. Order no:- {{var order_no}} Payment Amount:- {{var payment_amount}}");
                $credit_memo->setIsActive(1);
                $credit_memo->setStoreId($value);
                $credit_memo->setUseDefault(1);
                $credit_memo->setMainEntityId($credit_memo_main->getId());
                $credit_memo->save();

                $order_cancel = $objectManager->create("Emipro\Smsnotification\Model\Smsstore");
                $order_cancel->setSmsTitle("Order Cancelled");
                $order_cancel->setSmsEvents("order_cancel_after");
                $order_cancel->setSmsContent("Order Cancelled Successfully. Order no:- {{var order_no}}");
                $order_cancel->setIsActive(1);
                $order_cancel->setStoreId($value);
                $order_cancel->setUseDefault(1);
                $order_cancel->setMainEntityId($order_cancel_main->getId());
                $order_cancel->save();
            }
        }
    }

}
