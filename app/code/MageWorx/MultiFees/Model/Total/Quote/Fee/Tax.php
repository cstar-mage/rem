<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Total\Quote\Fee;

use Magento\Tax\Model\Config as TaxConfig;

class Tax extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $mageworxFeeAmount;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * @var \MageWorx\MultiFees\Helper\Price
     */
    protected $helperPrice;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelperData;

    /**
     * @var bool
     */
    protected $isCollected;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Fee constructor.
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Tax\Helper\Data $taxHelperData
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Tax\Helper\Data $taxHelperData,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->setCode('mageworx_fee_tax');
        $this->eventManager  = $eventManager;
        $this->storeManager  = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->helperData    = $helperData;
        $this->helperFee     = $helperFee;
        $this->helperPrice   = $helperPrice;
        $this->taxHelperData = $taxHelperData;
        $this->_logger       = $logger;
    }

    /**
     * Collect address fee amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return $this;
    }

    /**
     * Add fee total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($total->getMageworxFeeAmount()) {
            $taxMode = $this->helperData->getTaxInCart();

            if (in_array((int)$taxMode, [TaxConfig::DISPLAY_TYPE_BOTH, TaxConfig::DISPLAY_TYPE_INCLUDING_TAX])) {
                $applied = $total->getMageworxFeeDetails();
                if (is_string($applied)) {
                    $applied = unserialize($applied);
                }

                if ($applied) {
                    $result = [
                        'code'      => $this->getCode(),
                        'title'     => __('Additional Fees (Incl. Tax)'),
                        'value'     => $total->getMageworxFeeAmount(),
                        'full_info' => $applied,
                    ];

                    return $result;
                }
            }
        }

        return null;
    }
}
