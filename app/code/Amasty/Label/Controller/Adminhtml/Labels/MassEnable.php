<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Labels;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Amasty\Label\Model\ResourceModel\Labels\CollectionFactory;

/**
 * Class MassDelete
 */
class MassEnable extends MassActionAbstract
{

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $collection = $this->getCollection();
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $item->setStatus(1);
            $item->save();
        }

        $this->invalidateIndex();
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been changed.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
