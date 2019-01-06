<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Emipro\Smsnotification\Controller\Adminhtml\Smslog;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Index extends \Magento\Backend\App\Action {

    protected $resultPageFactory;

    public function __construct(
    Context $context, PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute() 
    {
        $resultPage = $this->resultPageFactory->create();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->create("Emipro\Smsnotification\Helper\Data")->validateSmsnotificationData();
        
        $resultPage->setActiveMenu('Emipro_Smsnotification::smsnotification');
        $resultPage->getConfig()->getTitle()->prepend(__('SMS Notification Log'));

        return $resultPage;
    }

}
