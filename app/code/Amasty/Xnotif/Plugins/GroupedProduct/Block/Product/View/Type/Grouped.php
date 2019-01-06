<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Plugins\GroupedProduct\Block\Product\View\Type;

use Magento\GroupedProduct\Block\Product\View\Type\Grouped as GroupedOrig;

class Grouped
{
    /**
     * @param GroupedOrig $subject
     * @param $result
     * @return string
     */
    public function afterGetTemplate(GroupedOrig $subject, $result)
    {
        if (strpos($result, 'type/grouped.phtml') !== false) {
            $result = "Amasty_Xnotif::product/view/type/grouped.phtml";
        }

        return $result;
    }
}
