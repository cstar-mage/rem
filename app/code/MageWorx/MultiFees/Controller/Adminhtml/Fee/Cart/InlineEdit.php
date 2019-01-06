<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Cart;

use MageWorx\MultiFees\Controller\Adminhtml\Fee\CartFee as FeeAbstractController;

class InlineEdit extends FeeAbstractController
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error      = false;
        $messages   = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error'    => true,
                ]
            );
        }

        foreach (array_keys($postItems) as $feeId) {
            /** @var \MageWorx\MultiFees\Model\CartFee $fee */
            $fee = $this->feeRepository->getById($feeId);
            try {
                $feeData = $this->filterData($postItems[$feeId]);
                $fee->addData($feeData);
                $this->feeRepository->save($fee);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorMessage($fee, $e->getMessage());
                $error      = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorMessage($fee, $e->getMessage());
                $error      = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorMessage(
                    $fee,
                    __('Something went wrong while saving the page.')
                );
                $error      = true;
            }
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error'    => $error
            ]
        );
    }

    /**
     * Add Fee id to error message
     *
     * @param \MageWorx\MultiFees\Model\CartFee $fee
     * @param string $errorText
     * @return string
     */
    protected function getError(\MageWorx\MultiFees\Model\CartFee $fee, $errorText)
    {
        return '[Fee ID: ' . $fee->getId() . '] ' . $errorText;
    }
}
