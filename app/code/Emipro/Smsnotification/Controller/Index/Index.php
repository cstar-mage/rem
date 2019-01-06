<?php

namespace Emipro\Smsnotification\Controller\Index;

use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class Index extends \Magento\Framework\App\Action\Action {

    protected $_productFactory;
    protected $_orderFactory;
    protected $_smsFactory;
    protected $scopeConfig;
    protected $_customerFactory;
    protected $websitesFactory;


    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Store\Model\StoreManagerInterface $storeConfig,
            CollectionFactory $websitesFactory
    ) {
        $this->_productFactory = $productFactory;
        $this->_orderFactory = $orderFactory;
        $this->scopeConfig = $storeConfig;
        $this->websitesFactory = $websitesFactory;
        parent::__construct($context);
    }

    public function execute() 
    {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
           $websites = $this->websitesFactory->create();
           foreach($websites as $web)
           {
               
           }       
    }

    protected function loadDefaultstore($event) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeevent = $objectManager->create('Emipro\Smsnotification\Model\ResourceModel\Smsevent\Collection');
        $storeevent->addFieldToSelect("sms_content");
        $storeevent->addFieldToFilter("sms_events", $event);
        return $storeevent;
    }

}
