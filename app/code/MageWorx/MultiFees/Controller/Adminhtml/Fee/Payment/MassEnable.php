<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Payment;

use MageWorx\MultiFees\Model\PaymentFee as FeeModel;

class MassEnable extends MassDisable
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 Fees have been enabled.';

    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while enabling Fees.';

    /**
     * @return int
     */
    protected function getActionValue()
    {
        return FeeModel::STATUS_ENABLED;
    }
}
