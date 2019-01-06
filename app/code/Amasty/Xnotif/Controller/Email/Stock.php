<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Email;

use Amasty\Xnotif\Model\Source\Group;
use Magento\Framework\Exception\LocalizedException;

class Stock extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    private $stockFactory;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    private $stockCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\StockFactory $stockFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
    }

    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        $guestEmail = $this->getRequest()->getParam('guest_email');
        $parentId = (int)$this->getRequest()->getParam('parent_id') ?: $productId;
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $redirect = $this->redirectFactory->create();
        if (!$backUrl) {
            $redirect->setUrl('/');
            return $redirect;
        }
        try {
            $product = $this->productRepository->getById($productId);

            /** @var \Magento\ProductAlert\Model\Stock $model */
            $model = $this->stockFactory->create()
                ->setProductId($product->getId())
                ->setWebsiteId($websiteId)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->setParentId($parentId);

            /** @var \Magento\ProductAlert\Model\ResourceModel\Stock\Collection $collection */
            $collection = $this->stockCollectionFactory->create()
                ->addWebsiteFilter($this->storeManager->getWebsite()->getId())
                ->addFieldToFilter('product_id', $productId)
                ->addStatusFilter(0)
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
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Not enough parameters.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update the alert subscription.'));
        }

        $redirect->setUrl($this->_redirect->getRefererUrl());
        return $redirect;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $allowedGroups = $this->scopeConfig->isSetFlag('amxnotif/stock/customer_group');
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
