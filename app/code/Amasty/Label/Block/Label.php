<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Block;

class Label extends \Magento\Framework\View\Element\Template
{
    const DISPLAY_PRODUCT  = 'display/product';
    const DISPLAY_CATEGORY = 'display/category';

    protected $randomKey = '';

    /**
     * @var \Amasty\Label\Helper\Config
     */
    private $helper;

    /**
     * @var \Amasty\Label\Model\Labels
     */
    protected $_label;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Label\Helper\Config $helper,
        array $data = []
    ) {
        $this->randomKey = rand();
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->setTemplate('Amasty_Label::label.phtml');
        if ($this->getData('label')) {
            $this->setLabel($this->getData('label'));

            $id  = $this->getLabel()->getProduct()->getId();
            $labelId = $this->getLabel()->getId();
            $this->addData([
                'cache_lifetime' => 86400,
                'cache_tags' => [
                    \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $id,
                    \Amasty\Label\Model\Labels::CACHE_TAG . '_' . $labelId,
                ],
            ]);
        }
    }

    public function getRandomKey()
    {
        return $this->randomKey;
    }

    public function getCacheKeyInfo()
    {
        return [
            'AMASTY_LABEL_BLOCK',
            $this->randomKey,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->getLabel()->getId(),
            $this->getLabel()->getMode(),
            $this->getLabel()->getProduct()->getId()
        ];
    }

    public function setLabel(\Amasty\Label\Model\Labels $label)
    {
        $this->_label = $label;
        return $this;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Get container path from module settings
     *
     * @return string
     */
    public function getContainerPath()
    {
        if ($this->_label->getMode() == 'cat') {
            $path= $this->helper->getModuleConfig(self::DISPLAY_CATEGORY);
        } else {
            $path = $this->helper->getModuleConfig(self::DISPLAY_PRODUCT);
        }

        return $path;
    }

    /**
     * Get image url withmode and site url
     *
     * @return string
     */
    public function getImageScr()
    {
        $img = $this->_label->getValue('img');
        return $this->helper->getImageUrl($img);
    }
}
