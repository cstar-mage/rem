<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Model;

use Magento\ProductAlert\Model\Email;

class Observer extends \Magento\ProductAlert\Model\Observer
{
    protected $_colFactorys = [];

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableType;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory $priceColFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory $stockColFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\ProductAlert\Model\EmailFactory $emailFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Registry $registry,
        \Amasty\Xnotif\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $catalogData,
            $scopeConfig,
            $storeManager,
            $priceColFactory,
            $customerRepository,
            $productRepository,
            $dateFactory,
            $stockColFactory,
            $transportBuilder,
            $emailFactory,
            $inlineTranslation
        );
        $this->_colFactorys['price'] = $priceColFactory;
        $this->_colFactorys['stock'] = $stockColFactory;
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->configurableType = $configurableType;
        $this->logger = $logger;
    }

    public function processWithLimit()
    {
        /** @var \Magento\ProductAlert\Model\Email $email */
        $email = $this->_emailFactory->create();
        $this->_sendNotifications('stock', $email);
        $this->_sendErrorEmail();

        return $this;
    }

    protected function _processStock(Email $email)
    {
        if (!$this->helper->enableQtyLimit()) {
            $this->_sendNotifications('stock', $email);
        }
    }

    protected function _processPrice(Email $email)
    {
        $this->_sendNotifications('price', $email);
    }

    protected function _sendErrorEmail()
    {
        if (count($this->_errors)) {
            foreach ($this->_errors as $error) {
                $this->logger->error($error);
            }
            parent::_sendErrorEmail();
        }
    }

    protected function _sendNotifications($type, Email $email)
    {
        try {
            $collection = $this->_colFactorys[$type]->create()
                ->addFieldToFilter('status', 0)
                ->setCustomerOrder();
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            return $this;
        }

        $prevCustomerEmail = null;
        $email->setType($type);
        $productJoinAlertData = [];

        foreach ($collection as $alert) {
            try {
                $websiteId = $alert->getWebsiteId();
                $website = $this->_storeManager->getWebsite($websiteId);
                $storeId = $alert->getStoreId() ? $alert->getStoreId() : $website->getDefaultStore()->getId();
                $email->setWebsite($website);

                if (!$this->_scopeConfig->getValue(
                    'catalog/productalert/allow_' . $type,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $website->getDefaultGroup()->getDefaultStore()->getId()
                )) {
                    continue;
                }

                $customer = $this->getCustomerFromAlert($alert);
                if ($customer->getEmail() !== $prevCustomerEmail) {
                    if ($prevCustomerEmail) {
                        $email->send();
                    }

                    $email->clean();
                    $email->setCustomerData($customer);
                }
                $prevCustomerEmail = $customer->getEmail();

                try {
                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $storeId
                    );
                } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
                    continue;
                }

                if ('stock' == $type) {
                    if ($this->helper->enableQtyLimit()) {
                        if (array_key_exists($alert->getProductId(), $productJoinAlertData)) {
                            if ($productJoinAlertData[$alert->getProductId()]['qty']
                                <= $productJoinAlertData[$alert->getProductId()]['counter']) {
                                continue;
                            } else {
                                $productJoinAlertData[$alert->getProductId()]['counter']++;
                            }
                        } else {
                            $productJoinAlertData[$alert->getProductId()] = [
                                'qty' => $this->helper->getProductQty($product),
                                'counter' => 1
                            ];
                        }
                    }

                    if ($product = $this->checkStockSubscription($product, $alert, $website)) {
                        $email->addStockProduct($product);
                    }
                } else {
                    if ($product = $this->checkPriceSubscription($product, $alert)) {
                        $email->addPriceProduct($product);
                    }
                }
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                continue;
            }
        }

        if ($prevCustomerEmail) {
            try {
                $email->send();
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
            }
        }

        return $this;
    }

    /**
     * @param $product
     * @param $alert
     * @return null
     */
    private function checkPriceSubscription($product, $alert)
    {
        if ($alert->getPrice() > $product->getFinalPrice()) {
            $productPrice = $product->getFinalPrice();
            $product->setFinalPrice(
                $this->_catalogData->getTaxPrice(
                    $product,
                    $productPrice
                )
            );
            $product->setPrice(
                $this->_catalogData->getTaxPrice(
                    $product,
                    $product->getPrice()
                )
            );

            $alert->setPrice($productPrice);
            $alert->setLastSendDate(
                $this->_dateFactory->create()->gmtDate()
            );
            $alert->setSendCount($alert->getSendCount() + 1);
            $alert->setStatus(1);
            $alert->save();

            return $product;
        }

        return null;
    }

    /**
     * @param $product
     * @param $alert
     * @param $website
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function checkStockSubscription($product, $alert, $website)
    {
        $minQuantity = (int)$this->helper->getModuleConfig('general/min_qty');
        $minQuantity = ($minQuantity < 1) ? 1 : $minQuantity;

        $isInStock = false;
        if ($product->canConfigure() && $product->isInStock()) {
            $allProducts = $this->configurableType->getUsedProducts($product);
            foreach ($allProducts as $simpleProduct) {
                $quantity = $this->helper->getProductQty($simpleProduct);
                $isInStock = ($simpleProduct->isSalable() || $simpleProduct->isSaleable())
                    && $quantity >= $minQuantity;
                if ($isInStock) {
                    break;
                }
            }
        } else {
            $quantity = $this->helper->getProductQty($product);
            $isInStock = $product->isSalable() && ($quantity >= $minQuantity);
        }

        if ($isInStock) {
            if ($alert->getParentId()
                && $alert->getParentId() != $alert->getProductId()
                && !$product->canConfigure()
            ) {
                $productParent = $this->productRepository->getById(
                    $alert->getParentId(),
                    false,
                    $website->getDefaultStore()->getId()
                );

                $product = $productParent;
            }

            $alert->setSendDate(
                $this->_dateFactory->create()->gmtDate()
            );
            $alert->setSendCount($alert->getSendCount() + 1);
            $alert->setStatus(1);
            $alert->save();

            return $product;
        }

        return null;
    }

    /**
     * @param $alert
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerFromAlert($alert)
    {
        if ($alert->getCustomerId()) {
            $customer = $this->customerRepository->getById(
                $alert->getCustomerId()
            );
        } else {
            $this->registry->unregister('amxnotif_data');
            $this->registry->register(
                'amxnotif_data',
                [
                    'guest' => 1,
                    'email' => $alert->getEmail()
                ]
            );

            try {
                $customer = $this->customerRepository->get(
                    $alert->getEmail(),
                    $this->_storeManager->getWebsite()->getId()
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $customer = $this->customerFactory->create()->getDataModel();
                $customer->setWebsiteId(
                    $this->_storeManager->getWebsite()->getId()
                )->setEmail(
                    $alert->getEmail()
                )->setLastname(
                    $this->helper->getModuleConfig('general/customer_name')
                )->setGroupId(
                    0
                )->setId(
                    0
                );
            }
        }

        $customer->setStoreId($alert->getStoreId());

        return $customer;
    }
}
