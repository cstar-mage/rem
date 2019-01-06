<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Checkout;

class ShippingFeeData extends AbstractFeeData
{
    /**
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees()
    {
        return $this->feeCollectionManager->getShippingFeeCollection();
    }
}
