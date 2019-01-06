<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
class Sendordersms extends \Magento\Framework\View\Element\Template {

    protected $_customerFactory;
    protected $formKey;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->_customerFactory = $customerFactory;
    }

    public function getOrderNumber()
    {
        return $this->getRequest()->getParam("order_id");
    }
    
    public function getFormKey() {
        return $this->formKey->getFormKey();
    }
    public function getPostformUrl() {
        return $this->_urlBuilder->getUrl("emipro_smsnotification/smssend/sendordersms");
    }
        
    public function getSmsData(){
        $orderId = $this->getOrderNumber();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $smslogValues = $objectManager->create("Emipro\Smsnotification\Model\Smslog")
                                      ->getCollection() 
                                      ->addFieldToFilter("order_id",$orderId)
                                      ->addFieldToFilter("message_type",3)
                                      ->setOrder('smslog_id','DESC');
        return $smslogValues->getData();
    }   
}
