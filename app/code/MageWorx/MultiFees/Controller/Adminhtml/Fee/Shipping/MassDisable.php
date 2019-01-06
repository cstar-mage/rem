<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Shipping;

use MageWorx\MultiFees\Model\ShippingFee as FeeModel;

class MassDisable extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 Fees have been disabled.';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while disabling Fees.';

    /**
     * @param FeeModel $fee
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function executeAction(FeeModel $fee)
    {
        $fee->setStatus($this->getActionValue());
        $this->feeRepository->save($fee);

        return $this;
    }

    /**
     * @return int
     */
    protected function getActionValue()
    {
        return FeeModel::STATUS_DISABLED;
    }
}
