<?php

namespace ModernRetail\OrderExporter\Controller\Adminhtml\Export;

class Index extends \Magento\Backend\App\AbstractAction
{

    protected $modelOrderExporter;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \ModernRetail\OrderExporter\Model\OrderExporter $modelOrderExporter
    ) {
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        $this->modelOrderExporter = $modelOrderExporter;
        parent::__construct($context);
    }

    public function execute() {

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->get('Magento\Sales\Model\Order')->load($orderId);
 
            $this->modelOrderExporter->createOrder($order);
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}