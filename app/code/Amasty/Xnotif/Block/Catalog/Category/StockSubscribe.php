<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Catalog\Category;

use \Magento\ProductAlert\Block\Product\View as ProductAlert;

class StockSubscribe extends ProductAlert
{
    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $xnotifHelper;

    public function __construct(
        \Amasty\Xnotif\Helper\Data $xnotifHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ProductAlert\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Helper\PostHelper $coreHelper,
        array $data = []
    ) {
        $this->xnotifHelper = $xnotifHelper;
        parent::__construct($context, $helper, $registry, $coreHelper, $data);
    }

    /**
     * Check if popup on
     *
     * @return int
     */
    public function usePopupForSubscribe()
    {
        return $this->xnotifHelper->getModuleConfig('stock/with_popup');
    }
}
