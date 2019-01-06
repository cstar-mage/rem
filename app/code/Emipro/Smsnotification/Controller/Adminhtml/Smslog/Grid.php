<?php
namespace Emipro\Smsnotification\Controller\Adminhtml\Smslog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory; 
use Magento\Framework\View\LayoutFactory ;
class Grid extends Action
{
	public function __construct(
	    Context $context,
        PageFactory $resultPageFactory,
        Rawfactory $resultRawFactory,
        LayoutFactory $layoutFactory
        )
	{
		parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
	}
	public function execute()
	{
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                'Emipro\Smsnotification\Block\Adminhtml\Smslog\Grid', 'grid.view.grid'
            )->toHtml()
        );
	}
}
