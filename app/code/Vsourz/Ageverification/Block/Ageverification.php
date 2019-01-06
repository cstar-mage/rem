<?php
namespace Vsourz\Ageverification\Block;
class Vsourz_Ageverification_Block_Ageverification extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vsourz\Ageverification\Helper\Data $helper,
        array $data = array()
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

}