<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */
namespace Amasty\Cart\Block\Product;

class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutCart, $productVisibility, $checkoutSession, $moduleManager, $data);
        $this->helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->setBlockType('related');
    }

    public function getHelper()
    {
        return $this->helper;
    }
}