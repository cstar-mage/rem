<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
class SendCustomer extends \Magento\Framework\View\Element\Template {

    protected $_customerFactory;
    protected $formKey;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, CollectionFactory $customerFactory,\Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->formKey = $formKey;
        parent::__construct($context);
        $this->_customerFactory = $customerFactory;
    }

   
    public function getFormKey() {
        return $this->formKey->getFormKey();
    }
    public function getPostformUrl() {
        return $this->_urlBuilder->getUrl("emipro_smsnotification/smssend/sendcustomersms");
    }
    public function getPostData()
    {
        return $this->getRequest()->getParam("id");
    }

    public function getSmsData()
    {
        $orderId = $this->getPostData();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $smslogValues = $objectManager->create("Emipro\Smsnotification\Model\Smslog")
                                      ->getCollection() 
                                      ->addFieldToFilter("customer_id",$orderId)
                                      ->addFieldToFilter("message_type",1)
                                      ->setOrder('smslog_id','DESC');
        return $smslogValues->getData();
    }
}
