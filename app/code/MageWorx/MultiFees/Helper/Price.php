<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Helper;

use Magento\Tax\Model\Config as TaxConfig;

/**
 * Config data helper
 */
class Price extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculator;

    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $helperCart;

    /**
     * Price constructor.
     *
     * @param \Magento\Tax\Model\Calculation $taxCalculator
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Tax\Model\Calculation $taxCalculator,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Helper\Cart $helperCart,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->taxCalculator = $taxCalculator;
        $this->helperFee     = $helperFee;
        $this->helperData    = $helperData;
        $this->helperCart    = $helperCart;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * @param float $price
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $taxClassId
     * @param null|\Magento\Quote\Model\Quote\Address $address
     * @return int
     */
    public function getTaxPrice($price, $quote, $taxClassId, $address = null)
    {
        if (!$quote) {
            return 0;
        }

        if (!$address) {
            $address = $this->getSalesAddress($quote);
        }

        $store             = $quote->getStore();
        $addressTaxRequest = $this->taxCalculator->getRateRequest(
            $address,
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $store
        );
        $addressTaxRequest->setProductClassId($taxClassId);
        $rate = $this->taxCalculator->getRate($addressTaxRequest);

        return $this->taxCalculator->calcTaxAmount($price, $rate, false, true);
    }

    /**
     * @param double|int $price
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $taxClassId
     * @param null $address
     * @return mixed
     */
    public function getPriceExcludeTax($price, $quote, $taxClassId, $address = null)
    {
        if (!$quote || !$taxClassId || !$price) {
            return $price;
        }

        if (!$address) {
            $address = $this->getSalesAddress($quote);
        }

        /** @var \Magento\Store\Model\Store $store */
        $store             = $quote->getStore();
        $addressTaxRequest = $this->taxCalculator->getRateRequest(
            $address,
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $store
        );
        $addressTaxRequest->setProductClassId($taxClassId);
        $rate = $this->taxCalculator->getRate($addressTaxRequest);

        return $this->priceCurrency->round($price / ((100 + $rate) / 100));
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return mixed
     */
    public function getSalesAddress($quote)
    {
        $address = $quote->getShippingAddress();
        if (!$address->getSubtotal()) {
            $address = $quote->getBillingAddress();
        }

        return $address;
    }

    /**
     * @param \MageWorx\MultiFees\Model\Option $option
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @return float|int|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionFormatPrice($option, $fee)
    {
        $price      = $option->getPrice();
        $taxClassId = $fee->getTaxClassId();

        $quote   = $this->helperFee->getQuote();
        $address = $this->getSalesAddress($quote);

        $percent = 0;
        if ($option->getPriceType() == \MageWorx\MultiFees\Model\AbstractFee::PERCENT_ACTION) {
            $percent = $price;

            $appliedTotals       = is_array($fee->getAppliedTotals()) ?
                $fee->getAppliedTotals() :
                explode(',', $fee->getAppliedTotals());
            $baseMageworxFeeLeft = $this->helperFee->getBaseFeeLeft(
                $address,
                $appliedTotals
            );

            $price   = ($baseMageworxFeeLeft > 0 && $percent > 0) ? ($baseMageworxFeeLeft * $percent) / 100 : 0;
            $percent = number_format(floatval($percent), 2, null, '') . '%';
        } else {
            if (!$fee->getIsOnetime()) {
                $price = $this->getQtyMultiplicationPrice($price);
            }
        }

        $store = $quote->getStore();
        $price = $this->priceCurrency->convert($price, $store); // base price - to store price

        // tax_calculation_includes_tax
        if ($this->helperData->isTaxCalculationIncludesTax()) {
            $priceInclTax = $price;
            $price        = $this->getPriceExcludeTax($price, $quote, $fee->getTaxClassId(), $address);
        } else {
            $priceInclTax = $price + $this->getTaxPrice($price, $quote, $taxClassId, $address);
        }

        $taxInBlock = $this->helperData->getTaxInBlock();

        if ($taxInBlock == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX) {
            $formatPrice = $percent ? $percent : $this->priceCurrency->format($price, false);

            return $formatPrice;
        }

        if ($taxInBlock == TaxConfig::DISPLAY_TYPE_INCLUDING_TAX) {
            $priceInclTax = $this->priceCurrency->format($priceInclTax, false);
            if ($percent) {
                $priceInclTax = $percent . ' (' . $priceInclTax . ')';
            }

            return $priceInclTax;
        }

        if ($taxInBlock == TaxConfig::DISPLAY_TYPE_BOTH) {
            $formatPrice  = $this->priceCurrency->format($price, false);
            $priceInclTax = $this->priceCurrency->format($priceInclTax, false);
            if ($percent) {
                return $percent;
            }

            return $formatPrice . ' (' . __('Incl. Tax %1', $priceInclTax) . ')';
        }
    }

    /**
     * @param double $price
     * @return double
     */
    public function getQtyMultiplicationPrice($price)
    {
        if ($this->helperCart->getItemsQty()) {
            return $price * intval($this->helperCart->getItemsQty());
        }

        return $price * intval($this->helperFee->getQuote()->getItemsQty());
    }
}
