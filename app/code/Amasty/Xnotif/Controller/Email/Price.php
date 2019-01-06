<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Email;

use Amasty\Xnotif\Model\Source\Group;
use Magento\Framework\Exception\LocalizedException;

class Price extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var \Magento\ProductAlert\Model\PriceFactory
     */
    private $priceFactory;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory
     */
    private $priceCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ProductAlert\Model\PriceFactory $priceFactory,
        \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceCollectionFactory
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->priceFactory = $priceFactory;
        $this->priceCollectionFactory = $priceCollectionFactory;
    }

    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        $guestEmail = $this->getRequest()->getParam('guest_email_price');
        $parentId = (int)$this->getRequest()->getParam('parent_id') ? : $productId;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $redirect = $this->redirectFactory->create();
        if (!$backUrl) {
            $redirect->setUrl('/');
            return $redirect;
        }

        try {
            $product = $this->productRepository->getById(
                $productId,
                false,
                $this->storeManager->getStore()->getWebsiteId()
            );

            /** @var \Magento\ProductAlert\Model\Price $model */
            $model = $this->priceFactory->create()
                ->setCustomerId($this->customerSession->getId())
                ->setProductId($product->getId())
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId($websiteId)
                ->setParentId($parentId);

            /** @var \Magento\ProductAlert\Model\ResourceModel\Price\Collection $collection */
            $collection = $this->priceCollectionFactory->create()
                ->getCollection()
                ->addWebsiteFilter($websiteId)
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('status', 0)
                ->setCustomerOrder();

            if ($guestEmail) {
                $guestEmail = filter_var($guestEmail, FILTER_SANITIZE_EMAIL);
                if (!\Zend_Validate::is($guestEmail, 'EmailAddress')) {
                    throw new LocalizedException(__('Please enter a valid email address.'));
                }

                try {
                    $customer = $this->customerRepository->get($guestEmail, $websiteId);
                    $model->setCustomerId($customer->getId());
                    $collection->addFieldToFilter('customer_id', $customer->getId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                    //this is guest
                    $model->setEmail($guestEmail);
                    $collection->addFieldToFilter('email', $guestEmail);
                }
            } else {
                $model->setCustomerId($this->customerSession->getId());
                $collection->addFieldToFilter('customer_id', $this->customerSession->getId());
            }

            if ($collection->getSize() > 0) {
                $this->messageManager->addSuccessMessage(__('Thank you! You are already subscribed to this product.'));
            } else {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update the alert subscription.'));
        }

        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $allowedGroups = $this->scopeConfig->isSetFlag('amxnotif/price/customer_group');
        $allowedGroups = str_replace(' ', '', $allowedGroups);
        $allowedGroups = explode(',', $allowedGroups);

        if (!in_array(Group::ALL_GROUPS, $allowedGroups)
            && !in_array(Group::NOT_LOGGED_IN_VALUE, $allowedGroups)
            && !$this->customerSession->authenticate($this)
        ) {
            $this->setFlag('', 'no-dispatch', true);
            if (!$this->customerSession->getBeforeUrl()) {
                $this->customerSession->setBeforeUrl($this->_getRefererUrl());
            }
        }
    }
}
