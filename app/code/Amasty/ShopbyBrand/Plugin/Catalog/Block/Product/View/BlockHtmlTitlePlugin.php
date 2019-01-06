<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\View;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Amasty\ShopbyBase\Plugin\Catalog\Block\Product\View\BlockHtmlTitlePluginAbstract;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Store\Model\StoreManagerInterface;

class BlockHtmlTitlePlugin extends BlockHtmlTitlePluginAbstract
{

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $brandHelper;

    /**
     * BlockHtmlTitlePlugin constructor.
     * @param CollectionFactory $optionCollectionFactory
     * @param Registry $registry
     * @param BlockFactory $blockFactory
     * @param Configurable $configurableType
     * @param \Amasty\ShopbyBase\Helper\Data $baseHelper
     * @param \Amasty\ShopbyBrand\Helper\Data $brandHelper
     */
    public function __construct(
        CollectionFactory $optionCollectionFactory,
        Registry $registry,
        BlockFactory $blockFactory,
        StoreManagerInterface $storeManager,
        Configurable $configurableType,
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper
    ) {
        parent::__construct($optionCollectionFactory, $registry, $storeManager, $blockFactory, $configurableType);
        $this->baseHelper = $baseHelper;
        $this->brandHelper = $brandHelper;
    }

    /**
     * Add Brand Label to Product Page
     *
     * @param \Magento\Theme\Block\Html\Title $original
     * @param $html
     * @return string
     */
    public function afterToHtml(\Magento\Theme\Block\Html\Title $original, $html)
    {
        if (!$this->baseHelper->isShopbyInstalled()) {
            $html = parent::afterToHtml($original, $html);
        }

        return $html;
    }

    /**
     * @return array
     */
    protected function getAttributeCodes()
    {
        return $this->brandHelper->getBrandAttributeCode() ? [$this->brandHelper->getBrandAttributeCode()] : [];
    }

    /**
     * @param OptionSetting $setting
     * @return string
     */
    protected function getOptionSettingUrl(OptionSetting $setting)
    {
        return $this->brandHelper->getBrandUrl($setting->getAttributeOption());
    }
}
