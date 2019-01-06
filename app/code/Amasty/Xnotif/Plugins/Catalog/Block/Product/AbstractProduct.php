<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Catalog\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct as ProductBlock;
use Magento\Catalog\Model\Product as ProductModel;
use Amasty\Xnotif\Block\Catalog\Category\StockSubscribe;

class AbstractProduct
{
    /**
     * @var string
     */
    private $loggedTemplate;

    /**
     * @var string
     */
    private $notLoggedTemplate;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\ProductAlert\Helper\Data
     */
    private $alertHelper;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $xnotifHelper;

    /**
     * @var ProductModel|null
     */
    private $product;

    /**
     * @var array
     */
    private $processedProducts;

    /**
     * @var string
     */
    private $blockName;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * AbstractProduct constructor.
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\ProductAlert\Helper\Data $alertHelper
     * @param \Amasty\Xnotif\Helper\Data $xnotifHelper
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\ProductAlert\Helper\Data $alertHelper,
        \Amasty\Xnotif\Helper\Data $xnotifHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->loggedTemplate = 'Magento_ProductAlert::product/view.phtml';
        $this->notLoggedTemplate = 'Amasty_Xnotif::category/subscribe.phtml';
        $this->sessionFactory = $sessionFactory;
        $this->alertHelper = $alertHelper;
        $this->xnotifHelper = $xnotifHelper;
        $this->processedProducts = array();
        $this->blockName = 'category.subscribe.block';
        $this->registry = $registry;
    }

    /**
     * @param ProductBlock $subject
     * @param ProductModel $product
     * @return array
     */
    public function beforeGetProductDetailsHtml(ProductBlock $subject, ProductModel $product)
    {
        $this->setProduct($product);

        return [$product];
    }

    /**
     * @param ProductBlock $subject
     * @param string $result
     * @return string
     */
    public function afterGetProductDetailsHtml(ProductBlock $subject, $result)
    {
        if ($this->enableSubscribe()) {
            $result .= $this->getSubscribeHtml($subject);
        }

        return $result;
    }

    public function beforeGetReviewsSummaryHtml(
        ProductBlock $subject,
        ProductModel $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        $this->setProduct($product);

        return [$product, $templateType, $displayIfNoReviews];
    }

    public function afterGetReviewsSummaryHtml(ProductBlock $subject, $result)
    {
        /** for category subscribe field render in afterGetProductDetailsHtml method */
        if ($subject->getNameInLayout() != 'category.products.list'
            && $this->enableSubscribe()
        ) {
            $result .= $this->getSubscribeHtml($subject);
        }

        return $result;
    }

    /**
     * @param ProductBlock $block
     * @return string
     */
    private function getSubscribeHtml(ProductBlock $block)
    {
        $subscribeBlock = $block->getLayout()->getBlock($this->blockName);
        if (!$subscribeBlock) {
            $subscribeBlock = $block->getLayout()->createBlock(StockSubscribe::class, 'category.subscribe.block');
        }

        if ($this->sessionFactory->create()->isLoggedIn()) {
            $subscribeBlock->setTemplate($this->loggedTemplate);
            $subscribeBlock->setHtmlClass('alert stock link-stock-alert');
            $subscribeBlock->setSignupLabel(
                __('Notify Me (Out Of Stock)')
            );
            $subscribeBlock->setSignupUrl(
                $this->alertHelper->setProduct($this->getProduct())->getSaveUrl('stock')
            );
        } else {
            $subscribeBlock->setTemplate($this->notLoggedTemplate);
            $subscribeBlock->setOriginalProduct($this->getProduct());
        }
        $this->processedProducts[] = $this->getProduct()->getId();
		
        return $subscribeBlock->toHtml();
    }

    /**
     * Check if need render subscribe block for current product
     *
     * @return bool
     */
    private function enableSubscribe()
    {
		$temp2 = $this->registry->registry('current_product');
		if(isset($temp2)){
			if($this->registry->registry('current_product')->getId() === $this->getProduct()->getId()){
				$temp = 0;
			} else {
				$temp = 1;
			}
		} else {
			$temp = 1;
		}
        $result = $this->xnotifHelper->allowForCurrentCustomerGroup('stock')
            && $this->xnotifHelper->getModuleConfig('stock/subscribe_category')
            && !$this->getProduct()->isSaleable()
            && !in_array($this->getProduct()->getId(), $this->processedProducts)
            && $temp;
			return $result;
    }

    /**
     * @return ProductModel|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param ProductModel $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }
}
