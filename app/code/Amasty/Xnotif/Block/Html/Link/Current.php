<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */

namespace Amasty\Xnotif\Block\Html\Link;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Amasty\Xnotif\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helper = $helper;
    }

    protected function _toHtml()
    {
        if (!$this->helper->allowForCurrentCustomerGroup('price')) {
            return '';
        }

        return parent::_toHtml();
    }
}
