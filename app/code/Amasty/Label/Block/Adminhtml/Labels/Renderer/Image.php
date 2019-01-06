<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Block\Adminhtml\Labels\Renderer;

use Magento\Framework\DataObject;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Amasty\Label\Helper\Config
     */
    private $helper;

    public function __construct(
        \Amasty\Label\Helper\Config $helper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @param DataObject $row
     * @return \Magento\Framework\Phrase|mixed|string
     */
    public function _getValue(DataObject $row)
    {
        $defaultValue = $this->getColumn()->getDefault();
        $data = parent::_getValue($row);
        $string = $data === null ? $defaultValue : $data;

        $url = $this->helper->getImageUrl($string);
        if ($url) {
            $string = '<img src="' . $url . '"
                            title="' . $string . '"
                            alt="' . $string . '"
                            style="max-width: 150px;"
                       >';
        } else {
            $string = __('---- none ----');
        }

        return $string;
    }
}
