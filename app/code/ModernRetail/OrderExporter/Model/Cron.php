<?php

namespace ModernRetail\OrderExporter\Model;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class Cron extends \Magento\Framework\Model\AbstractModel
{
    protected $_eventManager;
    protected $_orderCollectionFactory;
    protected $_creditmemoCollection;
    protected $_invoiceCollection;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection $creditmemoCollection,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection
    ){
        $this->_eventManager = $eventManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_creditmemoCollection = $creditmemoCollection;
        $this->_invoiceCollection = $invoiceCollection;
    }

    public function run()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');

        $enable =  $scopeConfig->getValue('order_exporter/api/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$enable) {
            return true;
        }

//        $_order = $objectManager->create('\Magento\Sales\Model\Order')->load(84);
//        $this->_eventManager->dispatch('modernretail_order_export',['order'=>$_order]);
//        die('STOP');

        $enableOrdersExport =  $scopeConfig->getValue('order_exporter/api/enable_orders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fromOrdersExport =  $scopeConfig->getValue('order_exporter/api/orders_from', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $enableInvoicesExport =  $scopeConfig->getValue('order_exporter/api/enable_invoices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fromInvoicesExport =  $scopeConfig->getValue('order_exporter/api/invoices_from', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $enableCreditmemosExport =  $scopeConfig->getValue('order_exporter/api/enable_creditmemos', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fromCreditmemosExport =  $scopeConfig->getValue('order_exporter/api/creditmemos_from', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $allowedStores = $objectManager->create('\ModernRetail\OrderExporter\Helper\Data')->getAllowedStores();

        $allowedStatuses = $objectManager->create('\ModernRetail\OrderExporter\Helper\Data')->getAllowedStatuses();

        if ($enableOrdersExport) {
            $orderCollection = $this->_orderCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('is_sent_to_middlware', 0);


            if ($fromOrdersExport) {
                $orderCollection->addAttributeToFilter('created_at',
                    array(
                        'from' => $fromOrdersExport.' 00:00:00',
                    ));
            }

            if ($allowedStores) {
                $orderCollection->addAttributeToFilter('store_id',
                    array(
                        'in' => $allowedStores, 
                    ));
            }


            if ($allowedStatuses) {
                $orderCollection->addAttributeToFilter('status',
                    array(
                        'in' => $allowedStatuses,
                    ));
            }

//            d($orderCollection->getSelect()->__toString());
            if (count($orderCollection)) {
                foreach ($orderCollection as $order) {
                    $_order = $objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                    $this->_eventManager->dispatch('modernretail_order_export',['order'=>$_order]);
                }
            }
        }


        if ($enableInvoicesExport) {
            $invoiceCollection = $this->_invoiceCollection
                ->addAttributeToSelect('*')
                ->addFieldToFilter('is_sent_to_middlware', 0);

            if ($fromInvoicesExport) {
                $invoiceCollection->addAttributeToFilter('created_at',
                    array(
                        'from' => $fromInvoicesExport.' 00:00:00',
                    ));
            }

            if ($allowedStores) {
                $invoiceCollection->addAttributeToFilter('store_id',
                    array(
                        'in' => $allowedStores,
                    ));
            }

            if (count($invoiceCollection)) {
                foreach ($invoiceCollection as $invoice) {
                    $_invoice = $objectManager->create('\Magento\Sales\Model\Order\Invoice')->load($invoice->getId());
                    $this->_eventManager->dispatch('modernretail_invoice_export',['invoice'=>$_invoice]);
                }
            }
        }

        if ($enableCreditmemosExport) {
            $creditmemoCollection = $this->_creditmemoCollection
                ->addAttributeToSelect('*')
                ->addFieldToFilter('is_sent_to_middlware', 0);

            if ($fromCreditmemosExport) {
                $creditmemoCollection->addAttributeToFilter('created_at',
                    array(
                        'from' => $fromCreditmemosExport.' 00:00:00',
                    ));
            }

            if ($allowedStores) {
                $creditmemoCollection->addAttributeToFilter('store_id',
                    array(
                        'in' => $allowedStores,
                    ));
            }

            if (count($creditmemoCollection)) {
                foreach ($creditmemoCollection as $creditmemo) {
                    $_creditmemo = $objectManager->create('\Magento\Sales\Model\Order\Creditmemo')->load($creditmemo->getId());
                    $this->_eventManager->dispatch('modernretail_creditmemo_export',['creditmemo'=>$_creditmemo]);
                }
            }
        }

        return true;
    }
}