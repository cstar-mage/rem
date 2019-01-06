<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\Data\PaymentFeeInterface;
use MageWorx\MultiFees\Exception\RefactoringException;

class PaymentFee extends AbstractFee implements PaymentFeeInterface
{
    const RESOURCE_MODEL_CLASS = 'MageWorx\MultiFees\Model\ResourceModel\PaymentFeeResource';

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageworx_multifees_payment_fee';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $cacheTag = 'mageworx_multifees_payment_fee';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_multifees_payment_fee';

    /**
     * Fee constructor.
     *
     * @param Fee\Condition\PaymentFee\CombineFactory $condCombineFactory
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineFactory
     * @param ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \MageWorx\MultiFees\Model\Fee\Condition\PaymentFee\CombineFactory $condCombineFactory,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperFee                  = $helperFee;
        $this->feeOptionCollectionFactory = $feeOptionCollectionFactory;
        $this->condCombineFactory         = $condCombineFactory;
        $this->condProdCombineFactory     = $condProdCombineFactory;
        $this->storeManager               = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get assigned shipping methods in case it is a shipping fee
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $methods = $this->getData(static::SHIPPING_METHODS);
        if (!$methods) {
            return [];
        } elseif (!is_array($methods)) {
            $methods = explode(',', $methods);
        }

        return $methods;
    }

    /**
     * Get assigned payment methods in case it is a payment fee
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $methods = $this->getData(static::PAYMENT_METHODS);
        if (!$methods) {
            return [];
        } elseif (!is_array($methods)) {
            $methods = explode(',', $methods);
        }

        return $methods;
    }

    /**
     * Assign shipping methods
     *
     * @param array $methods
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setShippingMethods($methods = [])
    {
        if (is_array($methods)) {
            $methods = implode(',', $methods);
        }

        return $this->setData(static::SHIPPING_METHODS, $methods);
    }

    /**
     * Assign payment methods
     *
     * @param array $methods
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setPaymentMethods($methods = [])
    {
        if (is_array($methods)) {
            $methods = implode(',', $methods);
        }

        return $this->setData(static::PAYMENT_METHODS, $methods);
    }

    /**
     * Check is fee valid for the address
     * Pre-check by shipping address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return bool
     * @throws RefactoringException
     */
    public function isValidForTheAddress($address)
    {
        if (!empty($this->getShippingMethods())) {
            if (!$address->getShippingMethod()) {
                return false;
            }
            if (!in_array($address->getShippingMethod(), $this->getShippingMethods())) {
                return false;
            }
        }

        if (!empty($this->getPaymentMethods())) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $address->getQuote();
            if (!$quote || !$quote instanceof \Magento\Quote\Model\Quote) {
                throw new RefactoringException(
                    __('Quote must be set and must be instance of \Magento\Quote\Model\Quote')
                );
            }

            /** @var \Magento\Quote\Model\Quote\Payment $payment */
            $payment = $quote->getPayment();
            if (!$payment || !$payment instanceof \Magento\Quote\Model\Quote\Payment) {
                throw new RefactoringException(
                    __('Payment must be set and must be instance of \Magento\Quote\Model\Quote\Payment')
                );
            }

            /** @var string $paymentMethod */
            $paymentMethod = $payment->getMethod();
            if (!$paymentMethod) {
                return false;
            }

            if (!in_array($paymentMethod, $this->getPaymentMethods())) {
                return false;
            }
        }

        return parent::isValidForTheAddress($address);
    }
}
