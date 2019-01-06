<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Checkout;

class CartFeeData extends AbstractFeeData
{
    /**
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMultifees()
    {
        return $this->feeCollectionManager->getCartFeeCollection();
    }
}
