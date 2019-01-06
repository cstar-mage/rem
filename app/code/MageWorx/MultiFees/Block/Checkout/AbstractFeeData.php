<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection;

abstract class AbstractFeeData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * @var \MageWorx\MultiFees\Helper\Price
     */
    protected $helperPrice;

    /**
     * @var \MageWorx\MultiFees\Api\FeeCollectionManagerInterface
     */
    protected $feeCollectionManager;

    /**
     * Form constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     * @param \MageWorx\MultiFees\Helper\Price $helperPrice
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feeCollectionManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\Data $helperData,
        \MageWorx\MultiFees\Helper\Price $helperPrice,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Customer\Model\Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \MageWorx\MultiFees\Api\FeeCollectionManagerInterface $feeCollectionManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession      = $checkoutSession;
        $this->customerSession      = $customerSession;
        $this->currency             = $currency;
        $this->priceCurrency        = $priceCurrency;
        $this->helperFee            = $helperFee;
        $this->helperData           = $helperData;
        $this->helperPrice          = $helperPrice;
        $this->sessionManager       = $sessionManager;
        $this->feeCollectionManager = $feeCollectionManager;
    }

    /**
     * Get specified fee data
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFeeData()
    {
        $fees = $this->getMultifees();
        // Remove hidden fees from count
        // "Select Fee" block should not be displayed if there was only a hidden fees inside
        $availableFeeCount = $fees->count(AbstractCollection::COUNT_EXCEPT_HIDDEN_FEE);

        $fee          = $this->checkoutSession->getQuote()->getMageworxFee();
        $feeFormatted = strip_tags($this->priceCurrency->convertAndFormat($fee));
        $details      = $this->helperFee->getQuoteDetailsMultifees();

        $result                     = [];
        $result['is_enable']        = $this->getIsEnable() ? (bool)$availableFeeCount : false;
        $result['is_display_title'] = ($result['is_enable'] == false) ? false : $this->getIsDisplayTitle();
        $result['fee']              = $feeFormatted;
        $result['url']              = $this->getUrl('multifees/checkout/fee');
        $result['is_valid']         = !isset($details['is_valid']) ? true : $details['is_valid'];
        $result['applyOnClick']     = $this->helperData->isApplyOnClick();

        return $result;
    }

    /**
     * Get corresponding fee collection
     *
     * @return AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    abstract protected function getMultifees();

    /**
     * @return bool
     */
    protected function getIsEnable()
    {
        return $this->helperData->isEnable();
    }

    /**
     * Check if display title
     * On the cart page we use the external title wrapper.
     *
     * @return boolean
     */
    protected function getIsDisplayTitle()
    {
        $actionList = [];
        if (!empty($this->_data['cart_full_actions']) && is_array($this->_data['cart_full_actions'])) {
            $actionList = $this->_data['cart_full_actions'];
        }
        $actionList[] = 'checkout_cart_index';

        return !in_array($this->_request->getFullActionName(), $actionList);
    }
}
