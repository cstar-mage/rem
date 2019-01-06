<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block;

use MageWorx\MultiFees\Helper\Data as Helper;
use MageWorx\MultiFees\Helper\Fee as HelperFee;
use MageWorx\MultiFees\Helper\Price as HelperPrice;
use MageWorx\MultiFees\Model\AbstractFee;
use MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollectionFactory;
use MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollectionFactory;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Magento\Checkout\Block\Checkout\AttributeMerger
     */
    protected $merger;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $regionCollection;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterface
     */
    protected $defaultShippingAddress = null;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var HelperFee
     */
    protected $helperFee;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * @var CartFeeCollectionFactory
     */
    protected $cartFeeCollectionFactory;

    /**
     * @var ShippingFeeCollectionFactory
     */
    protected $shippingFeeCollectionFactory;

    /**
     * @var PaymentFeeCollectionFactory
     */
    protected $paymentFeeCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var FeeFormInputPlant
     */
    protected $feeFormInputRendererFactory;

    /**
     * LayoutProcessor constructor.
     *
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $merger
     * @param Helper $helper
     * @param HelperFee $helperFee
     * @param HelperPrice $helperPrice
     * @param CartFeeCollectionFactory $cartFeeCollectionFactory
     * @param ShippingFeeCollectionFactory $shippingFeeCollectionFactory
     * @param PaymentFeeCollectionFactory $paymentFeeCollectionFactory
     * @param FeeFormInputPlant $feeFormInputRendererFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Checkout\Block\Checkout\AttributeMerger $merger,
        Helper $helper,
        HelperFee $helperFee,
        HelperPrice $helperPrice,
        CartFeeCollectionFactory $cartFeeCollectionFactory,
        ShippingFeeCollectionFactory $shippingFeeCollectionFactory,
        PaymentFeeCollectionFactory $paymentFeeCollectionFactory,
        \MageWorx\MultiFees\Block\FeeFormInputPlant $feeFormInputRendererFactory,
        \Magento\Framework\Escaper $escaper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->merger                       = $merger;
        $this->helper                       = $helper;
        $this->helperFee                    = $helperFee;
        $this->helperPrice                  = $helperPrice;
        $this->cartFeeCollectionFactory     = $cartFeeCollectionFactory;
        $this->shippingFeeCollectionFactory = $shippingFeeCollectionFactory;
        $this->paymentFeeCollectionFactory  = $paymentFeeCollectionFactory;
        $this->feeFormInputRendererFactory  = $feeFormInputRendererFactory;
        $this->escaper                      = $escaper;
        $this->logger                       = $logger;
    }

    /**
     * Add our multifees components to the layout if the specific container exists
     *
     * @param array $jsLayout
     * @return array
     * @TODO: Refactoring candidate: a lot of code
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    public function process($jsLayout)
    {
        $isApplyOnClick = $this->helper->isApplyOnClick();
        if (isset($jsLayout['components']['mageworx-fee-form-container'])) {
            $jsLayout['components']['mageworx-fee-form-container']['applyOnClick'] = $isApplyOnClick;
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
        )) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
            ['applyOnClick'] = $isApplyOnClick;
        }

        if (isset(
            $jsLayout['components']['mageworx-fee-form-container']['children']
            ['mageworx-fee-form-fieldset']['children']
        )
        ) {
            $fieldSetPointer = &$jsLayout['components']['mageworx-fee-form-container']['children']
            ['mageworx-fee-form-fieldset']['children'];

            try {
                $cartFeeComponents = $this->getCartFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $cartFeeComponents = [];
            }
            foreach ($cartFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
            ['children']['mageworx-fee-form-fieldset']['children']
        )
        ) {
            $fieldSetPointer = &$jsLayout['components']['checkout']['children']['sidebar']['children']
            ['summary']['children']['itemsBefore']['children']['mageworx-fee-form-container']
            ['children']['mageworx-fee-form-fieldset']['children'];

            try {
                $cartFeeComponents = $this->getCartFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $cartFeeComponents = [];
            }
            foreach ($cartFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shippingAdditional']['children']
            ['mageworx-shipping-fee-form-container']['children']['mageworx-shipping-fee-form-fieldset']['children']
        )
        ) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shippingAdditional']['children']
            ['mageworx-shipping-fee-form-container']['applyOnClick'] = $isApplyOnClick;
            $fieldSetPointer                                         = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shippingAdditional']['children']
            ['mageworx-shipping-fee-form-container']['children']['mageworx-shipping-fee-form-fieldset']['children'];

            try {
                $shippingFeeComponents = $this->getShippingFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $shippingFeeComponents = [];
            }
            foreach ($shippingFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['children']['mageworx-payment-fee-form-fieldset']['children']
        )
        ) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['applyOnClick'] = $isApplyOnClick;
            $fieldSetPointer                                        = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['beforeMethods']['children']
            ['mageworx-payment-fee-form-container']['children']['mageworx-payment-fee-form-fieldset']['children'];

            try {
                $paymentFeeComponents = $this->getPaymentFeeComponents();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e->getLogMessage());
                $paymentFeeComponents = [];
            }
            foreach ($paymentFeeComponents as $component) {
                $fieldSetPointer[] = $component;
            }
        }

        return $jsLayout;
    }

    /**
     * Get components for the available cart fees
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @TODO: Refactoring candidate: code duplication
     */
    private function getCartFeeComponents()
    {
        // Get multifees
        $quote      = $this->helperFee->getQuote();
        $address    = $this->helperFee->getSalesAddress($quote);
        $components = [];
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CartFeeCollection $feeCollection */
        $feeCollection = $this->cartFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $this->helperFee->getCustomerGroupId()
            )
            ->addRequiredFilter(false)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        /**
         * @var \MageWorx\MultiFees\Model\CartFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->isValidForTheAddress($address)) {
                $feeCollection->removeItemByKey($key);
            }

            $fee->setStoreId($quote->getStoreId());
            if ($fee->getEnableDateField() || $fee->getEnableCustomerMessage()) {
                $details = $this->helperFee->getQuoteDetailsMultifees();
                if ($fee->getEnableDateField()) {
                    $components[] = $this->getFeeDateComponent($fee, $details);
                }
                if ($fee->getEnableCustomerMessage()) {
                    $components[] = $this->getFeeCustomerMessageComponent($fee, $details);
                }
            }
        }
        // Get multifees end

        $components = array_merge($components, $this->convertFeeCollectionToComponentsArray($feeCollection));

        return $components;
    }

    /**
     * Get components for the available shipping fees
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @TODO: Refactoring candidate: code duplication
     */
    private function getShippingFeeComponents()
    {
        // Get multifees
        $quote   = $this->helperFee->getQuote();
        $address = $this->helperFee->getSalesAddress($quote);
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\ShippingFeeCollection $feeCollection */
        $feeCollection = $this->shippingFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $this->helperFee->getCustomerGroupId()
            )
            ->addRequiredFilter(false)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        /**
         * @var \MageWorx\MultiFees\Model\ShippingFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->isValidForTheAddress($address)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }

            $fee->setStoreId($quote->getStoreId());
        }
        // Get multifees end

        $components = $this->convertFeeCollectionToComponentsArray($feeCollection);

        return $components;
    }

    /**
     * Get components for the available payment fees
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @TODO: Refactoring candidate: code duplication
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    private function getPaymentFeeComponents()
    {
        // Get multifees
        $quote   = $this->helperFee->getQuote();
        $address = $quote->getBillingAddress();
        /** @var \MageWorx\MultiFees\Model\ResourceModel\Fee\PaymentFeeCollection $feeCollection */
        $feeCollection = $this->paymentFeeCollectionFactory->create();

        $feeCollection
            ->setValidationFilter(
                $quote->getStoreId(),
                $this->helperFee->getCustomerGroupId()
            )
            ->addRequiredFilter(false)
            ->addIsDefaultFilter(false)
            ->addIsActiveFilter()
            ->addSortOrder()
            ->addLabels();

        /**
         * @var \MageWorx\MultiFees\Model\PaymentFee $fee
         */
        foreach ($feeCollection as $key => $fee) {
            if (!$fee->isValidForTheAddress($address)) {
                $feeCollection->removeItemByKey($key);
                continue;
            }
            $fee->setStoreId($quote->getStoreId());
        }
        // Get multifees end

        $components = $this->convertFeeCollectionToComponentsArray($feeCollection);

        return $components;
    }

    /**
     * Create js-layout components for each fee in the collection
     * Not a fee specific method.
     *
     * @param \MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection $feeCollection
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function convertFeeCollectionToComponentsArray(
        \MageWorx\MultiFees\Model\ResourceModel\Fee\AbstractCollection $feeCollection
    ) {
        $details    = $this->helperFee->getQuoteDetailsMultifees();
        $components = [];
        /** @var \MageWorx\MultiFees\Model\AbstractFee $fee */
        foreach ($feeCollection as $fee) {
            /** @var \MageWorx\MultiFees\Block\FeeFormInput\FeeFormInputRenderInterface $renderer */
            $renderer     = $this->feeFormInputRendererFactory->create($fee, ['details' => $details]);
            $components[] = $renderer->render();
        }

        return $components;
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param array $details
     * @return array
     */
    private function getFeeDateComponent($fee, $details)
    {
        $isApplyOnClick = $this->helper->isApplyOnClick();
        if ($fee->getDateFieldTitle()) {
            $label = $fee->getDateFieldTitle();
        } else {
            $label = __('Date for') . ' "' . $fee->getTitle() . '"';
        }

        $scope                     = $this->getScope($fee->getType());
        $component                 = [];
        $component['component']    = 'MageWorx_MultiFees/js/form/element/date';
        $component['config']       = [
            'customScope' => $scope,
            'template'    => 'MageWorx_MultiFees/form/field',
            'elementTmpl' => 'ui/form/element/date'
        ];
        $component['dataScope']    = $scope . '.fee[' . $fee->getId() . '][date]';
        $component['label']        = $label;
        $component['provider']     = 'checkoutProvider';
        $component['visible']      = true;
        $component['validation']   = [];
        $component['applyOnClick'] = $isApplyOnClick;
        $component['sortOrder']    = $this->getSortOrder($fee->getSortOrder(), 2);

        if (!empty($details[$fee->getId()]['date'])) {
            $component['value'] = $this->escaper->escapeHtml($details[$fee->getId()]['date']);
        }

        return $component;
    }

    /**
     * @param \MageWorx\MultiFees\Model\AbstractFee $fee
     * @param array $details
     * @return array
     */
    private function getFeeCustomerMessageComponent($fee, $details)
    {
        $isApplyOnClick = $this->helper->isApplyOnClick();
        if ($fee->getCustomerMessageTitle()) {
            $label = $fee->getCustomerMessageTitle();
        } else {
            $label = __('Message for') . ' "' . $fee->getTitle() . '"';
        }

        $scope                  = $this->getScope($fee->getType());
        $component              = [];
        $component['component'] = 'MageWorx_MultiFees/js/form/element/textarea';
        $component['config']    = [
            'customScope' => $scope,
            'template'    => 'MageWorx_MultiFees/form/field',
        ];

        $component['dataScope']    = $scope . '.fee[' . $fee->getId() . '][message]';
        $component['label']        = $label;
        $component['provider']     = 'checkoutProvider';
        $component['visible']      = true;
        $component['validation']   = [];
        $component['applyOnClick'] = $isApplyOnClick;
        $component['sortOrder']    = $this->getSortOrder($fee->getSortOrder(), 1);

        if (!empty($details[$fee->getId()]['message'])) {
            $component['value'] = $this->escaper->escapeHtml($details[$fee->getId()]['message']);
        }

        return $component;
    }

    /**
     * @param int $feeSortOrder
     * @param int $i
     * @return mixed
     */
    protected function getSortOrder($feeSortOrder, $i = 0)
    {
        return $feeSortOrder * 5 + $i;
    }

    /**
     * Return scope for fee type
     *
     * @param int type
     * @return string
     */
    protected function getScope($type)
    {
        switch ($type) {
            case AbstractFee::CART_TYPE:
                return 'mageworxFeeForm';
            case AbstractFee::SHIPPING_TYPE:
                return 'mageworxShippingFeeForm';
            case AbstractFee::PAYMENT_TYPE:
                return 'mageworxPaymentFeeForm';
            default:
                return 'mageworxFeeForm';
        }
    }
}
