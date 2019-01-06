<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Helper;

use MageWorx\MultiFees\Api\CartFeeRepositoryInterface;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Api\PaymentFeeRepositoryInterface;
use MageWorx\MultiFees\Api\ShippingFeeRepositoryInterface;
use MageWorx\MultiFees\Exception\RefactoringException;
use MageWorx\MultiFees\Model\AbstractFee as FeeModel;

class Fee extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Error code for the required cart fee missed in the quote
     */
    const ERROR_REQUIRED_CART_FEE_MISS = 261503;

    /**
     * Error code for the required shipping fee missed in the quote
     */
    const ERROR_REQUIRED_SHIPPING_FEE_MISS = 261504;

    /**
     * Error code for the required payment fee missed in the quote
     */
    const ERROR_REQUIRED_PAYMENT_FEE_MISS = 261505;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $adminQuoteSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \MageWorx\MultiFees\Model\FeeFactory
     */
    protected $feeFactory;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Fee\CollectionFactory
     */
    protected $feeCollectionFactory;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory
     */
    protected $feeOptionCollectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var CartFeeRepositoryInterface
     */
    protected $cartFeeRepository;

    /**
     * @var ShippingFeeRepositoryInterface
     */
    protected $shippingFeeRepository;

    /**
     * @var PaymentFeeRepositoryInterface
     */
    protected $paymentFeeRepository;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connection;

    /**
     * @var CartFeeRepositoryInterface[]|PaymentFeeRepositoryInterface[]|ShippingFeeRepositoryInterface[]
     */
    protected $suitableRepositoriesByFeeId;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Fee constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Backend\Model\Session\Quote $adminQuoteSession
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param CartFeeRepositoryInterface $cartFeeRepository
     * @param ShippingFeeRepositoryInterface $shippingFeeRepository
     * @param PaymentFeeRepositoryInterface $paymentFeeRepository
     * @param \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\Quote $adminQuoteSession,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CartFeeRepositoryInterface $cartFeeRepository,
        ShippingFeeRepositoryInterface $shippingFeeRepository,
        PaymentFeeRepositoryInterface $paymentFeeRepository,
        \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->customerSession            = $customerSession;
        $this->checkoutSession            = $checkoutSession;
        $this->appState                   = $appState;
        $this->adminQuoteSession          = $adminQuoteSession;
        $this->dateFilter                 = $dateFilter;
        $this->objectManager              = $objectManager;
        $this->feeOptionCollectionFactory = $feeOptionCollectionFactory;
        $this->filterManager              = $filterManager;
        $this->dataObjectFactory          = $dataObjectFactory;
        $this->resourceConnection         = $resourceConnection;
        $this->connection                 = $resourceConnection->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );

        $this->cartFeeRepository     = $cartFeeRepository;
        $this->shippingFeeRepository = $shippingFeeRepository;
        $this->paymentFeeRepository  = $paymentFeeRepository;

        parent::__construct($context);
    }

    /**
     * Get customer group id, depend on current checkout session (admin, frontend)
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerGroupId()
    {
        $areaCode = $this->appState->getAreaCode();
        if ($areaCode == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $customer = $this->objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomer();

            return $customer->getGroupId();
        }

        return $this->customerSession->getCustomerGroupId();
    }

    /**
     * Get current checkout quote
     *
     * @return \Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote()
    {
        if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            $quote = $this->adminQuoteSession->getQuote();
        } else {
            $quote = $this->checkoutSession->getQuote();
        }

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param array $totalsArray
     *
     * @return int
     */
    public function getBaseFeeLeft($total, array $totalsArray)
    {
        $baseSubtotal = floatval($total->getBaseSubtotalWithDiscount());
        $baseShipping = floatval($total->getBaseShippingAmount()); // - $address->getBaseShippingTaxAmount()
        $baseTax      = floatval($total->getBaseTaxAmount());

        $baseMageWorxFeeLeft = 0;

        foreach ($totalsArray as $field) {
            switch ($field) {
                case 'subtotal':
                    $baseMageWorxFeeLeft += $baseSubtotal;
                    break;
                case 'shipping':
                    $baseMageWorxFeeLeft += $baseShipping;
                    break;
                case 'tax':
                    $baseMageWorxFeeLeft += $baseTax;
                    break;
            }
        }

        return $baseMageWorxFeeLeft;
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote $quote
     *
     * @param string $type
     * @return \Magento\Quote\Api\Data\AddressInterface|\Magento\Quote\Model\Quote\Address
     */
    public function getSalesAddress(
        \Magento\Quote\Api\Data\CartInterface $quote,
        $type = \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
    ) {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        switch ($type) {
            case \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_BILLING:
                $address = $quote->getBillingAddress();
                break;
            case \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING:
            default:
                $address = $quote->getShippingAddress();
                if (!$address->getSubtotal()) {
                    $address = $quote->getBillingAddress();
                }
        }

        return $address;
    }

    /**
     * @TODO: Remove old method later
     *
     * @param bool $onlyIsRequired
     * @param bool $onlyIsDefault
     * @param null|\Magento\Quote\Model\Quote $quote
     * @param null|\Magento\Quote\Model\Quote\Address $address
     * @param array $type
     *
     * @return void
     * @throws RefactoringException
     */
    public function getMultifees(
        $onlyIsRequired = false,
        $onlyIsDefault = false,
        $quote = null,
        $address = null,
        $type = [
            FeeModel::CART_TYPE,
            FeeModel::SHIPPING_TYPE,
            FeeModel::PAYMENT_TYPE
        ]
    ) {
        throw new RefactoringException(__('Old method getMultifees from helper has been called #261259'));
    }

    /**
     * @param FeeModel $fee
     * @param          $address
     *
     * @return bool
     */
    public function canProcessFee($fee, $address)
    {
        return $fee->getIsValidForAddress($address);
    }

    /**
     * $addressId = 0 - default address
     *
     * @param int $addressId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteDetailsMultifees($addressId = 0)
    {
        $session        = $this->getCurrentSession();
        $feeDetailsData = $session->getMageworxFeeDetails();
        if (is_null($feeDetailsData)) {
            return [];
        }
        $feeDetails = [];
        // get fees from default address
        if (isset($feeDetailsData[0])) {
            $feeDetails = $feeDetailsData[0];
        }

        // add fees from current address
        if ($addressId > 0 && isset($feeDetailsData[$addressId])) {
            foreach ($feeDetailsData[$addressId] as $feeId => $feeData) {
                $feeDetails[$feeId] = $feeData;
            }
        }

        return $feeDetails;
    }

    /**
     * @param array $details
     * @param int $addressId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setQuoteDetailsMultifees($details, $addressId = 0)
    {
        $session                = $this->getCurrentSession();
        $feeDetails             = $session->getMageworxFeeDetails() ? $session->getMageworxFeeDetails() : [];
        $feeDetails[$addressId] = $details;
        $session->setMageworxFeeDetails($feeDetails);
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote|\Magento\Checkout\Model\Session
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentSession()
    {
        $areaCode = $this->appState->getAreaCode();
        if ($areaCode == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this->objectManager->get('Magento\Backend\Model\Session\Quote');
        }

        return $this->checkoutSession;
    }

    /**
     * @param      $feesPost - data sent from the form
     * @param      $storeId
     * @param bool $collect
     * @param int $type - the type of a fee from the form: it is important for filtering the fees to be replaced
     *                       with exactly this type of fees and not all of types
     * @param int $addressId
     * @throws RefactoringException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addFeesToQuote(
        $feesPost,
        $storeId,
        $collect = true,
        $type = FeeInterface::CART_TYPE,
        $addressId = 0
    ) {
        $feesQuoteData = $this->getQuoteDetailsMultifees($addressId);
        $feesQuoteData = array_filter($feesQuoteData, 'is_array');
        if ($feesQuoteData) {
            foreach ($feesQuoteData as $feeId => $data) {
                if ($data['type'] == $type) {
                    // We remove fee with the current type, because they will be replaced with data from $feesPost
                    unset($feesQuoteData[$feeId]);
                }
            }
        }

        $feesPost      = !empty($feesPost) ? $feesPost : [];
        $feesQuoteData = $this->modifyFeesDetailsByPostData($feesQuoteData, $feesPost, $storeId);
        $feesQuoteData = array_filter($feesQuoteData, 'is_array');
        $this->setQuoteDetailsMultifees(
            $feesQuoteData,
            $addressId
        ); // update the fees in the session - add changes coming from the form

        if ($collect) {
            $this->getCurrentSession()->setTotalsCollectedFlag(false)->getQuote()->collectTotals();
        }
    }

    /**
     * Find and return repository by fee type
     *
     * @param int $type
     * @return CartFeeRepositoryInterface|PaymentFeeRepositoryInterface|ShippingFeeRepositoryInterface
     * @throws RefactoringException
     */
    public function getSuitableFeeRepositoryByType($type)
    {
        switch ($type) {
            case FeeInterface::CART_TYPE:
                $repository = $this->cartFeeRepository;
                break;
            case FeeInterface::SHIPPING_TYPE:
                $repository = $this->shippingFeeRepository;
                break;
            case FeeInterface::PAYMENT_TYPE:
                $repository = $this->paymentFeeRepository;
                break;
            default:
                throw new RefactoringException(__('Unspecified fee type to detect suitable repository #261840'));
        }

        return $repository;
    }

    /**
     * Returns suitable repository gor the fee using its ID
     * Algorithm: get fee type by its id using query to database
     * then find suitable repository by its type
     *
     * @param $feeId
     * @return CartFeeRepositoryInterface|PaymentFeeRepositoryInterface|ShippingFeeRepositoryInterface
     * @throws RefactoringException
     */
    public function getSuitableFeeRepositoryById($feeId)
    {
        if (!empty($this->suitableRepositoriesByFeeId[$feeId])) {
            return $this->suitableRepositoriesByFeeId[$feeId];
        }

        $select = $this->connection->select();
        $select->from($this->resourceConnection->getTableName('mageworx_multifees_fee'), [FeeInterface::TYPE]);
        $select->where('fee_id = ' . $feeId);
        $type = $this->connection->fetchOne($select);

        if (!$type) {
            throw new RefactoringException(__('Cant find type for the fee with id %1', $feeId));
        }

        $this->suitableRepositoriesByFeeId[$feeId] = $this->getSuitableFeeRepositoryByType($type);

        return $this->suitableRepositoriesByFeeId[$feeId];
    }

    /**
     * @param array $feesQuoteData
     * @param array $feesPost
     *
     * @param       $storeId
     * @return array
     * @throws RefactoringException
     */
    protected function modifyFeesDetailsByPostData($feesQuoteData, $feesPost, $storeId)
    {
        $filter = new \Zend_Filter();
        $filter->addFilter(new \Zend_Filter_StringTrim());
        $filter->addFilter(new \Zend_Filter_StripTags());

        foreach ($feesPost as $feeId => $feePostData) {
            $repository = $this->getSuitableFeeRepositoryById($feeId);
            /** @var FeeModel $feeModel */
            $feeModel = $repository->getById($feeId);

            if (empty($feePostData['options'])) {
                continue; // @protection: fee from the form has no options
            }

            if (!is_object($feeModel) || (is_object($feeModel) && !$feeModel->getId())) {
                continue; // @protection: fee does not exists
            }

            foreach ($feePostData['options'] as $optionId => $optionData) {
                $optionId = (int)$optionId;
                if (!$optionId) {
                    unset($feePostData['options'][$optionId]);
                    continue; // @protection: empty option
                }
                $opValue = [];

                /** @var \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $optionCollection */
                $optionCollection = $this->feeOptionCollectionFactory->create();
                $optionCollection->addFeeOptionFilter(
                    $optionId
                )->addFeeFilter(
                    $feeId
                )->addStoreLanguage(
                    $storeId,
                    true
                )->load();

                /** @var \MageWorx\MultiFees\Model\Option $option */
                $option = $optionCollection->getFirstItem();
                if (!$option || !$option->getId()) {
                    continue;
                }
                $opValue['title'] = $option->getTitle();

                if ($option->getPriceType() == 'percent') {
                    $opValue['percent'] = $option->getPrice();
                } else {
                    $opValue['base_price'] = $option->getPrice();
                }
                $feesQuoteData[$feeId]['options'][$optionId] = $opValue;
            }
            if (isset($feesQuoteData[$feeId]['options'])) {
                $feesQuoteData[$feeId]['title']         = $feeModel->getTitle();
                $feesQuoteData[$feeId]['date_title']    = $feeModel->getDateFieldTitle();
                $feesQuoteData[$feeId]['date']          = isset($feePostData['date']) ?
                    $filter->filter($feePostData['date']) :
                    '';
                $feesQuoteData[$feeId]['message_title'] = $feeModel->getCustomerMessageTitle();

                if (!empty($feePostData['message'])) {
                    $feesQuoteData[$feeId]['message'] = $this->filterManager->truncate(
                        $feePostData['message'],
                        ['length' => 1024]
                    );
                } else {
                    $feesQuoteData[$feeId]['message'] = '';
                }

                $feesQuoteData[$feeId]['applied_totals'] = explode(',', $feeModel->getAppliedTotals());
            }
            if (isset($feesQuoteData[$feeId])) {
                $feesQuoteData[$feeId]['type']         = $feeModel->getType();
                $feesQuoteData[$feeId]['is_onetime']   = $feeModel->getIsOnetime();
                $feesQuoteData[$feeId]['tax_class_id'] = $feeModel->getTaxClassId();
            }
        }

        $feesQuoteData = $this->filterMultiFeesInQuote($feesQuoteData, $storeId);

        // return all fees from the form with the current specified type, and other types that were already in the quote
        return $feesQuoteData;
    }

    /**
     * Helper method which filter the multi-fees in the quote and removes unacceptable values like:
     * 1) multi-fees having no options
     * 2) multi-fees with not valid options
     * 3) unavailable multi-fees model
     *
     * @param array $feesQuoteData
     * @param int $storeId
     * @return array
     * @throws RefactoringException
     */
    private function filterMultiFeesInQuote(array $feesQuoteData = [], $storeId = 0)
    {
        foreach ($feesQuoteData as $feeId => $data) {
            $repository = $this->getSuitableFeeRepositoryById($feeId);
            /** @var FeeModel $feeModel */
            $feeModel = $repository->getById($feeId);

            // The fee filter
            if (!is_object($feeModel) || (is_object($feeModel) && !$feeModel->getId())) {
                unset($feesQuoteData[$feeId]);
                continue; // @protection: fee does not exists
            }

            // The option filter
            foreach ($data['options'] as $optionId => $optionData) {
                $optionId = intval($optionId);
                if (!$optionId) {
                    unset($feesQuoteData[$feeId]['options'][$optionId]);
                    continue; // @protection: empty option
                }

                /** @var \MageWorx\MultiFees\Model\ResourceModel\Option\Collection $optionCollection */
                $optionCollection = $this->feeOptionCollectionFactory->create();
                $optionCollection->addFeeOptionFilter(
                    $optionId
                )->addFeeFilter(
                    $feeId
                )->load();
                /** @var \MageWorx\MultiFees\Model\Option $optionModel */
                $optionModel = $optionCollection->getFirstItem();
                if (!$optionModel || !$optionModel->getId()) {
                    unset($feesQuoteData[$feeId]['options'][$optionId]);
                    continue; // @protection: empty option
                }
            }

            // Check for an empty options must be after the option filter
            if (empty($feesQuoteData[$feeId]['options'])) {
                unset($feesQuoteData[$feeId]);
                continue; // @protection: fee from the form has no options
            }
        }

        return $feesQuoteData;
    }
}
