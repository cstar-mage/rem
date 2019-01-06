<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Block;

class Page extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Quickview\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Quickview\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->setTemplate('Amasty_Quickview::page.phtml');

    }

    public function closePopup()
    {
        return $this->helper->getModuleConfig('general/close_after_add');
    }

}
