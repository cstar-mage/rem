<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Helper;

use Amasty\Xnotif\Model\Source\Group;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Action;
use Magento\Productalert\Block\Product\View;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    
    /**
     * @var \Magento\ProductAlert\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\ProductAlert\Helper\Data $helper,
        \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $collectionFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->blockFactory = $blockFactory;
        $this->sessionFactory = $sessionFactory;
        $this->customerSession = $this->sessionFactory->create();
        $this->stockRegistry = $stockRegistry;
    }

    public function observeStockAlertBlock(ProductInterface $product, View $alertBlock)
    {
        $html = '';
        $currentProduct = $this->registry->registry('current_product');
        if (!$product->getId() || !$currentProduct) {
            return $html;
        }

        /*check if it is child product for replace product registered to child product.*/
        $isChildProduct = ($currentProduct->getId() != $product->getId());
        if ($isChildProduct) {
            $alertBlock->setData('parent_product_id', $currentProduct->getId());
            $alertBlock->setOriginalProduct($product);
        }

        if ($alertBlock && !$product->getData('amxnotif_hide_alert')) {
            if (!$this->sessionFactory->create()->isLoggedIn()) {
                $alertBlock->setTemplate('Amasty_Xnotif::product/view_email.phtml');
            }

            $alertBlock->setData('amxnotif_observer_triggered', 1);
            $html = $alertBlock->toHtml();
        }

        return $html;
    }

    public function getStockAlert(ProductInterface $product)
    {
        if (!$product || !$product->getId() || !$this->allowForCurrentCustomerGroup('stock')) {
            return '';
        }

        $alertBlock =  $this->blockFactory->createBlock(
            'Magento\ProductAlert\Block\Product\View',
            [
                'name' => 'productalert.stock.' . $product->getId()
            ]
        );

        $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
        $alertBlock->setSignupUrl($this->helper->setProduct($product)->getSaveUrl('stock'));
        $alertBlock->setHtmlClass('alert stock link-stock-alert');
        $alertBlock->setSignupLabel(__('Sign up to get notified when this configuration is back in stock'));

        return $this->observeStockAlertBlock($product, $alertBlock);
    }
    
    public function observePriceAlertBlock(ProductInterface $product, View $alertBlock)
    {
        if (!$product->getId()) {
            return '';
        }

        if ($alertBlock && !$this->sessionFactory->create()->isLoggedIn()) {
            /*set template with email input*/
            $alertBlock->setTemplate('Amasty_Xnotif::product/price/view_email.phtml');
            $alertBlock->setOriginalProduct($product);
        }

        $alertBlock->setData('amxnotif_observer_triggered', 1);

        return $alertBlock->toHtml();
    }

    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    public function getOriginalProduct($block)
    {
        $product = $block->getOriginalProduct();
        if (!$product) {
            $product = $this->getProduct();
        }

        return $product;
    }

    /**
     * @param $type
     * @return string
     */
    public function getSignupUrl($type)
    {
        return $this->_getUrl('xnotif/email/' . $type, [
            'product_id' => $this->getProduct()->getId(),
            'parent_id' => $this->registry->registry('par_product_id'),
            Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        ]);
    }

    /**
     * @param $type
     * @return string
     */
    public function getEmailUrl($type)
    {
        return $this->_getUrl('xnotif/email/' . $type);
    }

    public function addMessage()
    {
        $scheduleCollection = $this->collectionFactory->create()
            ->addFieldToFilter('job_code', ['eq' => 'amasty_catalog_product_alert']);
        $scheduleCollection->getSelect()->order("schedule_id desc");

        if ($scheduleCollection->getSize() == 0) {
            $this->messageManager->addNoticeMessage(
                __('No cron job "amasty_catalog_product_alert" found. Please check your cron configuration.')
            );
        }
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * @param $type
     * @return bool
     */
    public function allowForCurrentCustomerGroup($type)
    {
        $allowedGroups = $this->scopeConfig->getValue('amxnotif/' . $type . '/customer_group');
        $allowedGroups = str_replace(' ', '', $allowedGroups);
        $allowedGroups = explode(',', $allowedGroups);

        if (in_array(Group::ALL_GROUPS, $allowedGroups)) {
            return true;
        }

        return in_array($this->sessionFactory->create()->getCustomerGroupId(), $allowedGroups);
    }

    /**
     * @param $item
     * @return bool
     */
    public function isItemSalable($item)
    {
        $stockItem = $this->stockRegistry->getStockItem($item->getId(), 1);
        $saleable =  $stockItem->getIsInStock() && $this->verifyStock($stockItem);

        return $saleable;
    }

    /**
     * @param StockItemInterface $stockItem
     * @return bool
     */
    public function verifyStock(StockItemInterface $stockItem)
    {
        $result = true;

        if ($stockItem->getQty() === null && $stockItem->getManageStock()) {
            $result = false;
        }

        if ($stockItem->getBackorders() == StockItemInterface::BACKORDERS_NO
            && $stockItem->getQty() <= $stockItem->getMinQty()
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $item
     * @return null|string|string[]
     */
    public function getGroupedAlert($item)
    {
        $html = $this->getStockAlert($item);

        //remove form tag from content
        $html = preg_replace("/<\\/?" . 'form' . "(.|\\s)*?>/", '', $html);

        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getProductQty(\Magento\Catalog\Model\Product $product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $quantity = $stockItem->getQty();

        return $quantity;
    }

    /**
     * @return bool
     */
    public function enableQtyLimit()
    {
        return (bool)$this->getModuleConfig('stock/email_limit');
    }

    /**
     * @param $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'amxnotif/' . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
