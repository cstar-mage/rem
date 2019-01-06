<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Shipping;

use MageWorx\MultiFees\Model\ShippingFee as FeeModel;

class MassDelete extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';

    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param FeeModel $fee
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function executeAction(FeeModel $fee)
    {
        $this->feeRepository->delete($fee);

        return $this;
    }
}
