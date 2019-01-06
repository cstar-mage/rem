<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Plugin\Catalog\Block\Product\View;

use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class BlockHtmlTitlePluginAbstract
{
    const IMAGE_URL     = 'image_url';
    const LINK_URL      = 'link_url';
    const TITLE         = 'title';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection
     */
    private $optionSettingCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configurable
     */
    protected $configurableType;

    public function __construct(
        CollectionFactory $optionCollectionFactory,
        Registry $registry,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Configurable $configurableType
    ) {
        $this->optionSettingCollection = $optionCollectionFactory->create();
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        $this->configurableType = $configurableType;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    abstract protected function getAttributeCodes();

    /**
     * Add Brand Label to Product Page
     *
     * @param \Magento\Theme\Block\Html\Title $original
     * @param $html
     * @return string
     */
    public function afterToHtml(
        \Magento\Theme\Block\Html\Title $original,
        $html
    ) {
        $optionsData = $this->getOptionsData();
        if (!count($optionsData)) {
            return $html;
        }

        $block = $this->blockFactory->createBlock(\Magento\Framework\View\Element\Template::class)
            ->setData('options_data', $optionsData)
            ->setTemplate('Amasty_ShopbyBase::attribute/icon.phtml');
        $html = str_replace('/h1>', '/h1>' . $block->toHtml(), $html);
        return $html;
    }

    /**
     * @return array
     */
    private function getOptionsData()
    {
        $data = [];
        $product = $this->registry->registry('current_product');
        if (!$product) {
            return $data;
        }

        $attributeValues = $this->getAttributeValues($product);
        if (!count($attributeValues)) {
            return $data;
        }

        $optionSettingCollection = $this->optionSettingCollection
            ->addFieldToSelect(OptionSetting::TITLE)
            ->addFieldToSelect(OptionSetting::VALUE)
            ->addFieldToSelect(OptionSetting::SLIDER_IMAGE)
            ->addFieldToSelect(OptionSetting::IMAGE)
            ->addFieldToSelect(OptionSetting::FILTER_CODE)
            ->addFieldToFilter(OptionSetting::VALUE, ['in' => $attributeValues])
            ->addFieldToFilter(
                [OptionSetting::SLIDER_IMAGE, OptionSetting::IMAGE],
                [['neq' => ''],['neq' => '']]
            )
            ->addFieldToFilter(
                OptionSettingInterface::STORE_ID,
                [$this->storeManager->getStore()->getId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID]
            );

        //default_store options will be rewritten with current_store ones.
        $optionSettingCollection->getSelect()->order(['filter_code ASC', 'store_id ASC']);

        foreach ($optionSettingCollection as $optionSetting) {
            /** @var OptionSetting $optionSetting */
            $data[$optionSetting->getValue()] = $this->getOptionSettingData($optionSetting);
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getAttributeValues(\Magento\Catalog\Model\Product $product)
    {
        $values = [];
        $attributeCodes = $this->getAttributeCodes();
        if (!count($attributeCodes)) {
            return $values;
        }

        foreach ($attributeCodes as $code) {
            $data = $product->getData($code);
            if (!$data && $product->getTypeId() === Configurable::TYPE_CODE) {
                /** @var \Magento\Catalog\Model\Product[] $childProducts */
                $childProducts = $this->configurableType->getUsedProducts($product);
                foreach ($childProducts as $childProduct) {
                    if ($childProduct->getData($code)) {
                        $values = array_merge($values, explode(',', $childProduct->getData($code)));
                    }
                }
            } elseif ($data) {
                $values = array_merge($values, explode(',', $data));
            }
        }

        return $values;
    }

    /**
     * @param OptionSetting $setting
     * @return array
     */
    private function getOptionSettingData(OptionSetting $setting)
    {
        $data = [];
        $data[self::IMAGE_URL] = $setting->getSliderImageUrl();
        $data[self::LINK_URL] = $this->getOptionSettingUrl($setting);
        $data[self::TITLE] = $setting->getTitle();

        return $data;
    }

    /**
     * @param OptionSetting $setting
     * @return string
     */
    protected function getOptionSettingUrl(OptionSetting $setting)
    {
        return $setting->getUrlPath();
    }
}
