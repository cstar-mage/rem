<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Catalog\Category;

use Magento\Framework\View\Element\Template;

class Config extends Template
{
    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $xnotifHelper;

    public function __construct(
        \Amasty\Xnotif\Helper\Data $xnotifHelper,
        Template\Context $context,
        array $data = []
    ) {
        $this->xnotifHelper = $xnotifHelper;
        parent::__construct($context, $data);
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
