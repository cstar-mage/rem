<?php

namespace ModernRetail\OrderExporter\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Encryption\EncryptorInterface;

class SalesOrderSaveAfter implements ObserverInterface
{
    protected $_logger;

    protected $scopeConfig;

    protected $storeManager;

    protected $helperOrderExporter;

    protected $modelOrderExporter;

    protected $appState;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ModernRetail\OrderExporter\Helper\Data $helperOrderExporter,
        \ModernRetail\OrderExporter\Model\OrderExporter $modelOrderExporter,
        \Magento\Framework\App\State $appState,
        array $data = []
    ){
        $this->_logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->helperOrderExporter = $helperOrderExporter;
        $this->modelOrderExporter = $modelOrderExporter;
        $this->appState = $appState;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer){

        $enable =  $this->scopeConfig->getValue('order_exporter/api/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            if ($enable) {
                $order = $observer->getEvent()->getOrder();
                $this->modelOrderExporter->createOrder($order);
            }
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
}