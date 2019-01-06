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

class MassCustomerSend extends \Magento\Backend\App\Action {
    
    protected $_customerFactory;

    public function __construct(
    Context $context,CollectionFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->_customerFactory=$customerFactory;
    }

    public function execute() {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
        
    }

}
