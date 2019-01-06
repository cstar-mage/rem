<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Plugin;

class AbstractQuickView
{
    /**
     * @var \Amasty\Quickview\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    public function __construct(
        \Amasty\Quickview\Helper\Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
    }

    protected function addQuickViewBlock(&$result, $type = 'category')
    {
        $enable = $this->helper->getModuleConfig('general/enable');

        if ($enable) {
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(
                'Amasty\Quickview\Block\Config',
                'amasty.quickview.config',
                [ 'data' => [] ]
            );

            $html = $block->setPageType($type)->toHtml();
            $result .= $html;
        }
    }
}
