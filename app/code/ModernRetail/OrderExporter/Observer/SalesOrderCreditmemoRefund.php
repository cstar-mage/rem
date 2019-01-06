<?php

namespace ModernRetail\OrderExporter\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class SalesOrderCreditmemoRefund implements ObserverInterface {

    protected $logger;
    protected $customerFactory;
    protected $scopeConfig;
    protected $helperOrderExporter;
    protected $modelOrderExporter;
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ModernRetail\OrderExporter\Helper\Data $helperOrderExporter,
        \ModernRetail\OrderExporter\Model\OrderExporter $modelOrderExporter,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        $this->logger           = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->helperOrderExporter = $helperOrderExporter;
        $this->modelOrderExporter = $modelOrderExporter;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) {

//        $helperOrderExporter =  $this->helperOrderExporter->getRestrictedStores();
        $enable =  $this->scopeConfig->getValue('order_exporter/api/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            if ($enable) { //|| (in_array($this->storeManager->getStore()->getId(), $helperOrderExporter) && $this->appState->getAreaCode() !== 'adminhtml')
                $creditmemo = $observer->getEvent()->getCreditmemo();
                $this->modelOrderExporter->createCreditmemo($creditmemo);
            }
        } catch(Exception $e) {
            echo $e->getMessage();
        }        
    }
}