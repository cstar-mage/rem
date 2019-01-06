<?php

namespace ModernRetail\OrderExporter\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;
    protected $objectManager;

    const XML_PATH_CONFIG = 'order_exporter/api/'; 


    public function __construct(Context $context,
                                ObjectManagerInterface $objectManager,
                                StoreManagerInterface $storeManager
    )
    {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CONFIG . $code, $storeId);
    }

    public function getAllowedGroups()
    {
        $allowed_websites =  $this->getGeneralConfig('allowed_websites');
        $allowed_websites_arr = [];
        if ($allowed_websites) {
            $allowed_websites_arr = explode(',', $allowed_websites);
        }
        return $allowed_websites_arr;
    }

    public function getAllowedStores()
    {
        $allowed_stores = $this->getGeneralConfig('allowed_stores');
        $allowed_stores_arr = [];
        if ($allowed_stores) {
            $allowed_stores_arr = explode(',', $allowed_stores);
        }
        return $allowed_stores_arr;
    }

    public function getAllowedStatuses()
    {
        $allowed_stores = $this->getGeneralConfig('allowed_statuses');
        $allowed_stores_arr = [];
        if ($allowed_stores) {
            $allowed_stores_arr = explode(',', $allowed_stores);
        }
        return $allowed_stores_arr;
    }
}