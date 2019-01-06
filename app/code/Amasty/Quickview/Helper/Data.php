<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            'amasty_quickview/' . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getUrl($string, $data)
    {
        return $this->_getUrl($string, $data);
    }
}
