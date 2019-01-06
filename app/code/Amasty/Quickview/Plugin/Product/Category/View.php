<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Plugin\Product\Category;

class View extends \Amasty\Quickview\Plugin\AbstractQuickView
{
    public function afterGetProductListHtml(
        \Magento\Catalog\Block\Category\View $subject,
        $result
    ) {
        $this->addQuickViewBlock($result);

        return  $result;
    }
}
