<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{
    /**
     * Add fee name to order data
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        if ($address->getMageworxFeeAmount()) {
            $order->setMageworxFeeAmount($address->getMageworxFeeAmount());
            $order->setBaseMageworxFeeAmount($address->getBaseMageworxFeeAmount());
            $order->setMageworxFeeDetails($address->getMageworxFeeDetails());
            $order->setMageworxFeeTaxAmount($address->getMageworxFeeTaxAmount());
            $order->setBaseMageworxFeeTaxAmount($address->getBaseMageworxFeeTaxAmount());
        }

        return $this;
    }
}
