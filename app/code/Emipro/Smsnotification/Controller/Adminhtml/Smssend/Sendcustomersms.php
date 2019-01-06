<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Controller\Adminhtml\Smssend;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Emipro\Smsnotification\Helper\DataFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Sendcustomersms extends \Magento\Backend\App\Action {

    protected $_customerFactory;
    protected $_helper;
    protected $resultJsonFactory;

    public function __construct(
    Context $context, JsonFactory $resultJsonFactory, CollectionFactory $customerFactory, DataFactory $datafactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerFactory = $customerFactory;
        $this->_helper = $datafactory;

    }

    public function execute() 
    {
        $data = $this->getRequest()->getParams();
        $helper = $this->_helper->create();
        $msgtext = $data["message_text"];
        $post = $data["customerId"];

        /**/
        $customers = $this->_customerFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($post != '0') {
            $post = explode(",", $post);
            $customers->addAttributeToFilter("entity_id", array("in" => $post));
        }
        $all_data = array();
        foreach ($customers as $customer_single) {
            $message_text = $msgtext;
            if ($customer_single->getPrimaryBillingAddress()) {

                $number = $customer_single->getPrimaryBillingAddress()->getTelephone();
                $customerId = $customer_single->getPrimaryBillingAddress()->getCustomerId();
                $all_data["customer_email"] = $customer_single->getEmail();
                $all_data["customer_firstname"] = $customer_single->getFirstname();
                $all_data["customer_lastname"] = $customer_single->getLastname();
                
                $all_data["all_var"] = array("customer_email","customer_firstname","customer_lastname");

                foreach ($all_data["all_var"] as $value) 
                {
                    $message_text = str_replace("{{var ".$value."}}", $all_data[$value], $message_text);
                }
                $cutomerName= $customer_single->getPrimaryBillingAddress()->getCustomerName();
                $cutomerName= $customer_single->getPrimaryBillingAddress()->getCustomerName();

                $data = $helper->sendSms($number, $message_text, $customerId, $orderId = 0,$messageType=1);
                $result = $this->resultJsonFactory->create();
                $result->setData($data);
                return $result;
                
                }
            }
        }

        /**/
    }