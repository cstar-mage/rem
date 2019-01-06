<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Plugin\Product;

class ProductsList extends \Amasty\Quickview\Plugin\AbstractQuickView
{
    public function afterToHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $subject,
        $result
    ) {
        $this->addQuickViewBlock($result, 'widget');

        return  $result;
    }
}
