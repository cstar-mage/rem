<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Plugin;

class AddFeePaypal
{
    const AMOUNT_SUBTOTAL = 'subtotal';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->registry        = $registry;
        $this->priceCurrency   = $priceCurrency;
    }


    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @return array
     */
    public function afterGetAmounts($cart, $result)
    {
        $total = $result;
        $quote = $this->checkoutSession->getQuote();

        if ($cart instanceof \Magento\Paypal\Model\Cart) {
            $feePrice = $this->_getMageWorxFeeAmount($quote);

            if ($feePrice > 0) {
                $total[self::AMOUNT_SUBTOTAL] = $total[self::AMOUNT_SUBTOTAL] + $feePrice;
            }
        }

        return $total;
    }

    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @return array
     */
    public function beforeGetAllItems($cart)
    {
        $paypalTest = $this->registry->registry('is_paypal_items') ? $this->registry->registry(
            'is_paypal_items'
        ) : 0;
        $quote      = $this->checkoutSession->getQuote();

        if ($paypalTest < 1 &&
            $cart instanceof \Magento\Paypal\Model\Cart &&
            method_exists($cart, 'addCustomItem')
        ) {
            $feePrice = $this->_getMageWorxFeeAmount($quote);

            if ($feePrice > 0) {
                $cart->addCustomItem(__("Fee"), 1, $feePrice);
                $reg     = $this->registry->registry('is_paypal_items');
                $current = $reg + 1;
                $this->registry->unregister('is_paypal_items');
                $this->registry->register('is_paypal_items', $current);
            }
        }
    }

    /**
     * @param $quote
     * @return float
     */
    protected function _getMageWorxFeeAmount($quote)
    {
        $feeBasePrice = $quote->getShippingAddress()->getData('base_mageworx_fee_amount');

        return $feeBasePrice;
    }
}