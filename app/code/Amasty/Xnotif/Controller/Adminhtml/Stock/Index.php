<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Xnotif\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->resultPageFactory->create();
        $layout = $pageResult->getLayout();

        $pageResult->setActiveMenu('Amasty_Xnotif::amxnotif_stock');
        $pageResult->addBreadcrumb(__('Alerts'), __('Stock Alerts'));
        $pageResult->addContent($layout->createBlock('Amasty\Xnotif\Block\Adminhtml\Stock'));

        /* Add message about cron job*/
        $this->helper->addMessage();

        $pageResult->getConfig()->getTitle()->prepend(__('Stock Alerts '));

        return $pageResult;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::stock');
    }
}
