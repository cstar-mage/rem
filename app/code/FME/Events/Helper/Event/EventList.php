<?php

namespace FME\Events\Helper\Event;

class EventList
{

    const XML_PATH_LIST_MODE = 'catalog/frontend/list_mode';
    const VIEW_MODE_LIST = 'list';
    const VIEW_MODE_GRID = 'grid';
    const DEFAULT_SORT_DIRECTION = 'asc';

    protected $scopeConfig;
    protected $_defaultAvailableLimit  = [10 => 10,20 => 20,50 => 50];
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getEventAvailableViewMode()
    {
        switch ($this->scopeConfig->getValue(self::XML_PATH_LIST_MODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            case 'grid':
                $availableMode = ['grid' => __('Grid')];
                break;

            case 'list':
                $availableMode = ['list' => __('List')];
                break;

            case 'grid-list':
                $availableMode = ['grid' => __('Grid'), 'list' =>  __('List')];
                break;

            case 'list-grid':
                $availableMode = ['list' => __('List'), 'grid' => __('Grid')];
                break;
            default:
                $availableMode = null;
                break;
        }

        return $availableMode;
    }

    public function getEventDefaultViewMode($options = [])
    {
        if (empty($options)) {
            $options = $this->getEventAvailableViewMode();
        }
        return current(array_keys($options));
    }

    public function getEventDefaultSortField()
    {
        return $this->scopeConfig->getValue(
            \Magento\Catalog\Model\Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getEventAvailableLimit($mode)
    {
        if (!in_array($mode, [self::VIEW_MODE_GRID, self::VIEW_MODE_LIST])) {
            return $this->_defaultAvailableLimit;
        }
        $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
        $perPageValues = (string)$this->scopeConfig->getValue(
            $perPageConfigKey,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->scopeConfig->isSetFlag(
            'catalog/frontend/list_allow_all',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return ($perPageValues + ['all' => __('All')]);
        } else {
            return $perPageValues;
        }
    }

    public function getEventDefaultLimitPerPageValue($viewMode)
    {
        if ($viewMode == self::VIEW_MODE_LIST) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/list_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } elseif ($viewMode == self::VIEW_MODE_GRID) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/grid_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return 0;
    }
}
