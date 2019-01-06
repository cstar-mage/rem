<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Total\Invoice;

class Fee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {

        $order = $invoice->getOrder();

        if ($order->getMageworxFeeAmount() > 0 &&
            $order->getMageworxFeeInvoiced() < ($order->getMageworxFeeAmount() - $order->getMageworxFeeCanceled())
        ) {
            $invoice->setMageworxFeeAmount(
                (float)$order->getMageworxFeeAmount() -
                (float)$order->getMageworxFeeInvoiced() -
                $order->getMageworxFeeCanceled()
            );
            $invoice->setBaseMageworxFeeAmount(
                (float)$order->getBaseMageworxFeeAmount() -
                (float)$order->getBaseMageworxFeeInvoiced() -
                $order->getBaseMageworxFeeCanceled()
            );
            $invoice->setMageworxFeeTaxAmount($order->getMageworxFeeTaxAmount());
            $invoice->setBaseMageworxFeeTaxAmount($order->getBaseMageworxFeeTaxAmount());
            $invoice->setMageworxFeeDetails($order->getMageworxFeeDetails());

            $invoice->setGrandTotal(
                $invoice->getGrandTotal() + $invoice->getMageworxFeeAmount()
            );
            $invoice->setBaseGrandTotal(
                $invoice->getBaseGrandTotal() +
                $invoice->getBaseMageworxFeeAmount()
            );
        } else {
            $invoice->setMageworxFeeAmount(0);
            $invoice->setBaseMageworxFeeAmount(0);
            $invoice->setMageworxFeeTaxAmount(0);
            $invoice->setBaseMageworxFeeTaxAmount(0);
            $invoice->setMageworxFeeDetails('');
        }

        return $this;
    }
}
