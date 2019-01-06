<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Plugin\XmlSitemap\ShopbyBase\Model;

use Amasty\ShopbyBase\Plugin\XmlSitemap\Model\Sitemap as NativeSitemap;

class Sitemap
{
    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $helperUrl;

    public function __construct(
        \Amasty\ShopbySeo\Helper\Url $helperUrl
    ) {
        $this->helperUrl = $helperUrl;
    }

    /**
     * @param NativeSitemap $subject
     * @param $url
     * @return string
     */
    public function afterApplySeoUrl(NativeSitemap $subject, $url)
    {
        if ($this->helperUrl->isSeoUrlEnabled()) {
            $url = $this->helperUrl->seofyUrl($url);
        }

        return $url;
    }
}
