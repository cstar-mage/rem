<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Plugin\CatalogSearch;

class Result extends \Amasty\Quickview\Plugin\AbstractQuickView
{
    public function afterGetProductListHtml(
        \Magento\CatalogSearch\Block\Result $subject,
        $result
    ) {
        $this->addQuickViewBlock($result);

        return  $result;
    }
}
