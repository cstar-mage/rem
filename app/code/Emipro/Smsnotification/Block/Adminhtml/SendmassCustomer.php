<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Block\Adminhtml;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
class SendmassCustomer extends \Magento\Framework\View\Element\Template {

    protected $_customerFactory;
    protected $formKey;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, CollectionFactory $customerFactory,\Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->formKey = $formKey;
        parent::__construct($context);
        $this->_customerFactory = $customerFactory;
    }

    public function getCustomerLabel() {
        $post=$this->getRequest()->getPost("selected",0);
        $CustomerData=array();
        $customer=$this->_customerFactory->create();
        if((int)$post!=0)
        {
            $customer->addAttributeToFilter("entity_id",array("in"=>$post));    
        }
        foreach($customer as $customer_single)
        {
            if($customer_single->getPrimaryBillingAddress())
                array_push($CustomerData,array("email"=>$customer_single->getEmail(),"mobile"=>$customer_single->getPrimaryBillingAddress()->getTelephone()));
            
        }
        return $CustomerData;
    }
    
    public function getFormKey() {
        return $this->formKey->getFormKey();
    }
    public function getPostformUrl() {
        return $this->_urlBuilder->getUrl("emipro_smsnotification/smssend/sendsms");
    }
    public function getPostData()
    {
        $post=$this->getRequest()->getPost("selected",0);
        return $post;
    }

}
