<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Total\Pdf;

class Fee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * Retrieve Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getSource()->getMageworxFeeAmount();
    }
}
