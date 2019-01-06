<?php
namespace FME\Events\Block;

use FME\Events\Helper\Event\EventList;
use FME\Events\Model\Event\EventList\EventToolbar as ToolbarModel;

class EventToolbar extends \Magento\Framework\View\Element\Template
{
    
    protected $_collection = null;
    protected $_availableOrder = null;
    protected $_availableMode = [];
    protected $_enableViewSwitcher = true;
    protected $_isExpanded = true;
    protected $_orderField = null;
    protected $_direction = EventList::DEFAULT_SORT_DIRECTION;
    protected $_viewMode = null;
    protected $_paramsMemorizeAllowed = true;
    protected $_template = 'events/list/eventtoolbar.phtml';
    protected $_catalogSession;
    protected $_toolbarModel;
    protected $_productListHelper;
    protected $urlEncoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,        
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        EventList $productListHelper,        
        array $data = []
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_productListHelper = $productListHelper;

        parent::__construct($context, $data);
    }

    public function disableParamsMemorizing()
    {
        $this->_paramsMemorizeAllowed = false;
        return $this;
    }
    
    protected function _memorizeParamEvent($param, $value)
    {
        if ($this->_paramsMemorizeAllowed && !$this->_catalogSession->getParamsMemorizeDisabled()) {
            $this->_catalogSession->setData($param, $value);
        }
        return $this;
    }
    
    public function setCollectionEvent($collection)
    {
        $this->_collection = $collection;
        $this->_collection->setCurPage($this->getCurrentPageEvent());
        $limit = (int)$this->getLimitEvent();

        if ($limit) {
            $this->_collection->setPageSize($limit);
        }

        if ($this->getCurrentOrderEvent()) {
            $this->_collection->setOrder($this->getCurrentOrderEvent(), $this->getCurrentDirectionEvent());
        }
        return $this;
    }

    public function getCollectionEvent()
    {
        return $this->_collection;
    }

    public function getCurrentPageEvent()
    {
        return $this->_toolbarModel->getEventCurrentPage();
    }

    public function getCurrentOrderEvent()
    {
        $order = $this->_getData('_current_grid_order');

        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrdersEvent();

        $defaultOrder = $this->getOrderField();

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->_toolbarModel->getEventOrder();

        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        if ($order != $defaultOrder) {
            $this->_memorizeParamEvent('sort_order', $order);
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }

    public function getCurrentDirectionEvent()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = ['asc', 'desc'];
        $dir = strtolower($this->_toolbarModel->getEventDirection());
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }

        if ($dir != $this->_direction) {
            $this->_memorizeParamEvent('sort_direction', $dir);
        }

        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }

    public function setDefaultOrderEvent($field)
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$field])) {
            $this->_orderField = $field;
        }
        return $this;
    }
    
    public function setDefaultDirectionEvent($dir)
    {
        if (in_array(strtolower($dir), ['asc', 'desc'])) {
            $this->_direction = strtolower($dir);
        }
        return $this;
    }

    public function getAvailableOrdersEvent()
    {
        $this->loadAvailableOrders();
        return $this->_availableOrder;
    }
    
    public function setAvailableOrdersEvent($orders)
    {
        $this->_availableOrder = $orders;
        return $this;
    }

    public function addOrderToAvailableOrders($order, $value)
    {
        $this->loadAvailableOrders();
        $this->_availableOrder[$order] = $value;
        return $this;
    }

    public function removeOrderFromAvailableOrders($order)
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$order])) {
            unset($this->_availableOrder[$order]);
        }
        return $this;
    }

    public function isOrderCurrentEvent($order)
    {
        return $order == $this->getCurrentOrderEvent();
    }

    public function getPagerUrlEvent($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = true;
        $urlParams['_escape'] = false;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    public function getPagerEncodedUrl($params = [])
    {
        return $this->urlEncoder->encode($this->getPagerUrlEvent($params));
    }

    public function getCurrentModeEvent()
    {
       
        $mode = $this->_getData('_current_grid_mode');
        if ($mode) {
            return $mode;
        }
        $defaultMode = $this->_productListHelper->getEventDefaultViewMode($this->getModesEvent());
        $mode = $this->_toolbarModel->getEventMode();
        if (!$mode || !isset($this->_availableMode[$mode])) {
            $mode = $defaultMode;
        }

        $this->setData('_current_grid_mode', $mode);
        return $mode;
    }

    public function isModeActive($mode)
    {
        return $this->getCurrentModeEvent() == $mode;
    }

    public function getModesEvent()
    {
        if ($this->_availableMode === []) {
            $this->_availableMode = $this->_productListHelper->getEventAvailableViewMode();
        }
        return $this->_availableMode;
    }

    public function setModesEvent($modes)
    {
        $this->getModesEvent();
        if (!isset($this->_availableMode)) {
            $this->_availableMode = $modes;
        }
        return $this;
    }

    public function disableViewSwitcher()
    {
        $this->_enableViewSwitcher = false;
        return $this;
    }

    public function enableViewSwitcherEvent()
    {
        $this->_enableViewSwitcher = true;
        return $this;
    }

    public function isEnabledViewSwitcherEvent()
    {
        return $this->_enableViewSwitcher;
    }

    public function disableExpanded()
    {
        $this->_isExpanded = false;
        return $this;
    }

    public function enableExpanded()
    {
        $this->_isExpanded = true;
        return $this;
    }

    public function isExpandedEvent()
    {
        return $this->_isExpanded;
    }

    public function getDefaultPerPageValueEvent()
    {
        if ($this->getCurrentModeEvent() == 'list' && ($default = $this->getDefaultListPerPage())) {
            return $default;
        } elseif ($this->getCurrentModeEvent() == 'grid' && ($default = $this->getDefaultGridPerPage())) {
            return $default;
        }
        return $this->_productListHelper->getEventDefaultLimitPerPageValue($this->getCurrentModeEvent());
    }

    public function getEventAvailableLimit()
    {
        return $this->_productListHelper->getEventAvailableLimit($this->getCurrentModeEvent());
    }

    public function getLimitEvent()
    {
        $limit = $this->_getData('_current_limit');
        if ($limit) {
            return $limit;
        }

        $limits = $this->getEventAvailableLimit();
        $defaultLimit = $this->getDefaultPerPageValueEvent();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->_toolbarModel->getEventLimit();
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        if ($limit != $defaultLimit) {
            $this->_memorizeParamEvent('limit_page', $limit);
        }

        $this->setData('_current_limit', $limit);
        return $limit;
    }

    public function isLimitCurrentEvent($limit)
    {
        return $limit == $this->getLimitEvent();
    }
    
    public function getFirstNumEvent()
    {
        $collection = $this->getCollectionEvent();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }
    public function getLastNum()
    {
        $collection = $this->getCollectionEvent();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }
    
    public function getTotalNumEvent()
    {
        return $this->getCollectionEvent()->getSize();
    }

    
    public function isFirstPage()
    {
        return $this->getCollectionEvent()->getCurPage() == 1;
    }

    public function getLastPageNumEvent()
    {
        return $this->getCollectionEvent()->getLastPageNumber();
    }

    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('product_list_toolbar_pager');

        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            $pagerBlock->setAvailableLimit($this->getEventAvailableLimit());
            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimitEvent()
            )->setCollectionEvent(
                $this->getCollectionEvent()
            );
            return $pagerBlock->toHtml();
        }
        return '';
    }

    public function getWidgetOptionsJsonEvent(array $customOptions = [])
    {
        $defaultMode = $this->_productListHelper->getEventDefaultViewMode($this->getModesEvent());
        $options = [
            'mode' => ToolbarModel::MODE_PARAM_NAME,
            'direction' => ToolbarModel::DIRECTION_PARAM_NAME,
            'order' => ToolbarModel::ORDER_PARAM_NAME,
            'limit' => ToolbarModel::LIMIT_PARAM_NAME,
            'modeDefault' => $defaultMode,
            'directionDefault' => $this->_direction ?: ProductList::DEFAULT_SORT_DIRECTION,
            'orderDefault' => $this->_productListHelper->getEventDefaultSortField(),
            'limitDefault' => $this->_productListHelper->getEventDefaultLimitPerPageValue($defaultMode),
            'url' => $this->getPagerUrlEvent(),
        ];
        $options = array_replace_recursive($options, $customOptions);
        return json_encode(['productListToolbarForm' => $options]);
    }

    protected function getOrderField()
    {
        if ($this->_orderField === null) {
            $this->_orderField = $this->_productListHelper->getEventDefaultSortField();
        }
        return $this->_orderField;
    }

    private function loadAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = [
            'event_start_date' => 'Start Date',
            'event_end_date'   => 'End Date',
            'event_name'       => 'Event Name'
            ];
        }
        return $this;
    }
}
