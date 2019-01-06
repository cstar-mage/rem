<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Payment;

use MageWorx\MultiFees\Controller\Adminhtml\Fee\PaymentFee as FeeAbstractController;

class Edit extends FeeAbstractController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \MageWorx\MultiFees\Model\AbstractFee $fee */
        $fee          = $this->initFee();
        $feeRequestId = $fee->getId();

        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->initAction();

        if ($this->getRequest()->getParam('fee_id') && !$feeRequestId) {
            $this->messageManager->addErrorMessage(__('The Fee no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('mageworx_multifees/*');

            return $resultRedirect;
        }

        $title = $fee->getId() ? $fee->getTitle() : __('New Payment Fee');
        $resultPage->getConfig()->getTitle()->append($title);
        $data = $this->_session->getData('mageworx_multifees_fee_data', true);

        if (!empty($data)) {
            $fee->setData($data);
        }

        return $resultPage;
    }
}
