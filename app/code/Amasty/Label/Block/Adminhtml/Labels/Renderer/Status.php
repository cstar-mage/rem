<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Block\Adminhtml\Labels\Renderer;

use Magento\Framework\DataObject;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return mixed
     */
    public function _getValue(DataObject $row)
    {
        $value = parent::_getValue($row);
        if ($value) {
            $string = '<span class="grid-severity-notice"><span>'
                    . __('Active')
                . '</span></span>';
        } else {
            $string = '<span class="grid-severity-critical"><span>'
                    . __('Inactive')
                . '</span></span>';
        }

        return $string;
    }
}
