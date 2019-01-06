<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Plugin\XmlSitemap\Model;

use Amasty\Shopby\Helper\FilterSetting;
use Amasty\XmlSitemap\Model\Sitemap as NativeSitemap;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product;

class Sitemap
{
    /**
     * @var ObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $optionSetting;

    public function __construct(
        ObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionSetting
    ) {

        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
        $this->optionSetting = $optionSetting;
    }

    public function aroundGetBrandCollection(NativeSitemap $subgect, \Closure $proceed, $storeId)
    {
        $result = [];
        $attrCode   = $this->scopeConfig->getValue(
            'amshopby_brand/general/attribute_code',
            ScopeInterface::SCOPE_STORE
        );

        if (!$attrCode) {
            return $result;
        }

        $brandAttribute = $this->getAttributeByCode($attrCode);

        foreach ($brandAttribute->getSource()->getAllOptions() as $option) {
            if ($option['value']) {
                $url = $this->optionSetting->getSettingByValue(
                    $option['value'],
                    FilterSetting::ATTR_PREFIX . $attrCode,
                    $storeId
                )->getUrlPath();

                $url = $this->applySeoUrl($url);
                $url = $this->removeSid($url);

                $result[] = $this->dataObjectFactory->create()->setUrl($url);
            }
        }

        return $result;
    }

    /**
     * Overrided in Amasty/ShopbySeo/Plugin/XmlSitemap/ShopbyBase/Model/Sitemap.php
     * @param $url
     * @return mixed
     */
    public function applySeoUrl($url)
    {
        return $url;
    }

    /**
     * @param string $attrCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($attrCode)
    {
        return $this->eavConfig->getAttribute(Product::ENTITY, $attrCode);
    }

    /**
     * @param $url
     * @return bool|string
     */
    private function removeSid($url)
    {
        $baseUrl = substr($url, 0, strpos($url, '?'));
        if (!$baseUrl) {
            return $url;
        }
        $parsed = [];
        parse_str(substr($url, strpos($url, '?') + 1), $parsed);
        if (isset($parsed['SID'])) {
            $url = $baseUrl;
            unset($parsed['SID']);
            if (!empty($parsed)) {
                $url .= '?' . http_build_query($parsed);
            }
        }

        return $url;
    }
}
