<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Block;

use Magento\Framework\App\ActionInterface;

class Config extends \Magento\Framework\View\Element\Template
{
    const LINK_TEMPLATE = '<img class="am-quickview-icon" src="%s"/>%s';

    /**
     * @var \Amasty\Quickview\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $urlEncoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Quickview\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->urlEncoder = $urlEncoder;
        $this->setTemplate('Amasty_Quickview::config.phtml');
    }

    public function javascriptParams()
    {
        $additional = [];
        $addUrlKey = ActionInterface::PARAM_NAME_URL_ENCODED;
        $addUrlValue = $this->_urlBuilder->getUrl('*/*/*', ['_use_rewrite' => true, '_current' => true]);
        $additional[$addUrlKey] = $this->urlEncoder->encode($addUrlValue);

        $params = [
            'url'  =>  $this->helper->getUrl('amasty_quickview/ajax/view', $additional),
            'text' =>  $this->getViewHtml(),
            'css'  =>  $this->helper->getModuleConfig('general/custom_css_styles')
        ];

        return $this->jsonEncoder->encode($params);
    }

    private function getViewHtml()
    {
        return sprintf(
            self::LINK_TEMPLATE,
            $this->getIconImage(),
            $this->getViewText()
        );
    }

    private function getViewText()
    {
         return $this->helper->getModuleConfig('general/view_text');
    }

    private function getIconImage()
    {
        return $this->getViewFileUrl('Amasty_Quickview::images/len.png');
    }

}