<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Productalert;

class View
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Xnotif\Helper\Data $helper
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Productalert\Block\Product\View $subject
     * @param $html
     * @return string
     */
    public function afterToHtml(\Magento\Productalert\Block\Product\View $subject, $html)
    {
        $type = $subject->getNameInLayout();
        if ($html == '') {
            return $html;
        }

        if ($type == 'productalert.stock' && !$subject->getData('amxnotif_observer_triggered')) {
            if (!$this->helper->allowForCurrentCustomerGroup('stock')) {
                return '';
            }

            $product = $this->registry->registry('current_product');
            if (!$product->isSaleable()) {
                $html = $this->helper->observeStockAlertBlock($product, $subject);

                return $html;
            }
        }

        if ($type == 'productalert.price' && !$subject->getData('amxnotif_observer_triggered')) {
            if (!$this->helper->allowForCurrentCustomerGroup('price')) {
                return '';
            }

            $product = $this->registry->registry('current_product');
            $html = $this->helper->observePriceAlertBlock($product, $subject);
        }

        return $html;
    }
}
