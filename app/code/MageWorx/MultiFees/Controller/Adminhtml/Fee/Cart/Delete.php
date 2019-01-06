<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Cart;

use MageWorx\MultiFees\Controller\Adminhtml\Fee\CartFee as FeeAbstractController;

class Delete extends FeeAbstractController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $fee            = $this->initFee();
        $feeId          = $fee->getId();
        if ($feeId) {
            try {
                $feeName = $fee->getTitle();
                $this->feeRepository->delete($fee);
                $this->messageManager->addSuccessMessage(__("The Fee has been deleted."));
                $this->_eventManager->dispatch(
                    'adminhtml_mageworx_multifees_fee_on_delete',
                    ['status' => 'success', 'fee_id' => $feeId, 'keyword' => $feeName]
                );
                $resultRedirect->setPath('mageworx_multifees/*/');
            } catch (\Exception $e) {
                $feeName = empty($feeName) ? '' : $feeName;
                $this->_eventManager->dispatch(
                    'adminhtml_mageworx_multifees_fee_on_delete',
                    ['status' => 'fail', 'fee_id' => $feeId, 'keyword' => $feeName]
                );
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('mageworx_multifees/*/edit', ['fee_id' => $feeId]);

                return $resultRedirect;
            }

            return $resultRedirect;
        }
        $this->messageManager->addErrorMessage(__('Fee not found.'));
        $resultRedirect->setPath('mageworx_multifees/*/');

        return $resultRedirect;
    }
}
