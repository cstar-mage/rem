<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api;

interface FeeCollectionManagerInterface
{
    /**
     * Get collection of the available cart fees for the current quote address
     *
     * @important Returns loaded collection
     *
     * @param bool $required
     * @param bool $isDefault
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCartFeeCollection($required = false, $isDefault = false);

    /**
     * Get only required cart fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\CartFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredCartFees();

    /**
     * Get collection of the available shipping fees for the current quote address
     *
     * @important Returns loaded collection
     *
     * @param bool $required
     * @param bool $isDefault
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShippingFeeCollection($required = false, $isDefault = false);

    /**
     * Get only required shipping fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\ShippingFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredShippingFees();

    /**
     * Get collection of the available payment fees for the current quote address
     *
     * @important Returns loaded collection
     *
     * @param bool $required
     * @param bool $isDefault
     * @return \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentFeeCollection($required = false, $isDefault = false);

    /**
     * Get only required payment fees for the current quote address
     *
     * @return \Magento\Framework\DataObject[]|\MageWorx\MultiFees\Model\PaymentFee[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequiredPaymentFees();

    /**
     * Set quote for the future validation
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \MageWorx\MultiFees\Api\FeeCollectionManagerInterface
     */
    public function setQuote(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * Get currently stored quote which used for the fee validation
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote();

    /**
     * Set quote address for which fees should be valid before manager return them
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return \MageWorx\MultiFees\Api\FeeCollectionManagerInterface
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Get currently stored quote address which used for the fee validation
     *
     * @param string $type \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getAddress($type = \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING);

    /**
     * Remove cached data
     *
     * @return \MageWorx\MultiFees\Api\FeeCollectionManagerInterface
     */
    public function clean();
}
