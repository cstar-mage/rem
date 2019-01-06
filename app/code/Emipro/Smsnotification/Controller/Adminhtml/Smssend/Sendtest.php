<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Controller\Adminhtml\Smssend;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Emipro\Smsnotification\Helper\DataFactory;

class Sendtest extends \Magento\Backend\App\Action {

    protected $_helper;

    public function __construct(
    Context $context, DataFactory $datafactory
    ) {
        parent::__construct($context);
        $this->_helper = $datafactory;
    }

    public function execute() 
    {
        $post=$this->getRequest()->getParams();
        $helper=$this->_helper->create();

        if(isset($post["smstext"]) && isset($post["smsnumber"]))
        {
            $data = $helper->sendSms($post["smsnumber"], $post["smstext"], null, null,0);
        }
    }

}
