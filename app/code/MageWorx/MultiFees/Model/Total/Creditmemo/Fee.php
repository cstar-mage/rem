<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Total\Creditmemo;

class Fee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this|void
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getMageworxFeeAmount() > 0 && $order->getMageworxFeeRefunded() < $order->getMageworxFeeInvoiced()) {
            $creditmemo->setMageworxFeeAmount(
                (float)$order->getMageworxFeeInvoiced() - (float)$order->getMageworxFeeRefunded()
            );
            $creditmemo->setBaseMageworxFeeAmount(
                (float)$order->getBaseMageworxFeeInvoiced() - (float)$order->getBaseMageworxFeeRefunded()
            );
            $creditmemo->setMageworxFeeTaxAmount($order->getMageworxFeeTaxAmount());
            $creditmemo->setBaseMageworxFeeTaxAmount($order->getBaseMageworxFeeTaxAmount());
            $creditmemo->setMageworxFeeDetails($order->getMageworxFeeDetails());

            $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $creditmemo->getMageworxFeeTaxAmount());
            $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $creditmemo->getBaseMageworxFeeTaxAmount());
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getMageworxFeeAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseMageworxFeeAmount());
        } else {
            $creditmemo->setMageworxFeeAmount(0);
            $creditmemo->setBaseMageworxFeeAmount(0);
            $creditmemo->setMageworxFeeTaxAmount(0);
            $creditmemo->setBaseMageworxFeeTaxAmount(0);
            $creditmemo->setMageworxFeeDetails('');
        }

        return $this;
    }
}
