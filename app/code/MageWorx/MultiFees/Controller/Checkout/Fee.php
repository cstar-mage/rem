<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Checkout;

class Fee extends \Magento\Framework\App\Action\Action
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \MageWorx\MultiFees\Helper\Fee
     */
    protected $helperFee;

    /**
     * @var \MageWorx\MultiFees\Helper\BillingAddressManager
     */
    protected $billingAddressManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Fee constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \MageWorx\MultiFees\Helper\BillingAddressManager $billingAddressManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->quoteRepository       = $quoteRepository;
        $this->helperFee             = $helperFee;
        $this->billingAddressManager = $billingAddressManager;
        $this->storeManager          = $storeManager;
        $this->cart                  = $cart;
        $this->logger                = $logger;
    }

    /**
     * Initialize fees
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $feesPost = $this->getPreparedFeePostData();
        $type     = $this->getRequest()->getPost('type');
        $storeId  = $this->storeManager->getStore()->getId();

        if ($this->getRequest()->getPost('billingAddressData')) {
            $this->billingAddressManager->transferBillingAddressDataToTheAddressObject(
                $this->getRequest()->getPost('billingAddressData')
            );
        }

        try {
            $this->helperFee->addFeesToQuote($feesPost, $storeId, true, $type, 0);
            $cartQuote  = $this->cart->getQuote();
            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $this->quoteRepository->save($cartQuote);
            }
        } catch (\Exception $e) {
            $this->logger->error($e);

            return;
        }

        return $this->getResponse()->setBody(\Zend_Json::encode(['result' => 'true']));
    }

    /**
     * Prepare post data array: convert options to array where the key of each option was option id
     *
     * @return array
     */
    private function getPreparedFeePostData()
    {
        $feesPost = $this->getRequest()->getPost('fee');
        if (empty($feesPost)) {
            return [];
        }

        foreach ($feesPost as $feeId => $feeData) {
            if (empty($feeData['options'])) {
                continue;
            }

            $options    = is_array($feeData['options']) ? $feeData['options'] : explode(',', $feeData['options']);
            $newOptions = [];
            foreach ($options as $option) {
                $newOptions[$option] = [];
            }

            $feesPost[$feeId]['options'] = $newOptions;
        }

        return $feesPost;
    }
}
