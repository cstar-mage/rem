<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Controller\Adminhtml\Smssend;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Emipro\Smsnotification\Helper\DataFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Sendordersms extends \Magento\Backend\App\Action {

    protected $_customerFactory;
    protected $_helper;
    protected $resultJsonFactory;

    public function __construct(
    Context $context, JsonFactory $resultJsonFactory, 
    CollectionFactory $customerFactory, DataFactory $datafactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerFactory = $customerFactory;
        $this->_helper = $datafactory;

    }

    public function execute() 
    {
        $post=$this->getRequest()->getParams();
        $helper=$this->_helper->create();

        if(isset($post["order_test"]))
        {
            $data = $helper->processOrderSmsText($post["message"],$post["order_id"],$messageType=3);
            $result = $this->resultJsonFactory->create();
            $result->setData($data);
            return $result;
        }
        else
        {
            $data = $helper->processOrderSmsText($post["message"],$post["order_id"],$messageType=1);
        }
    }
}
