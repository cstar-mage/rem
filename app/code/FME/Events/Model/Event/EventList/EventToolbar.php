<?php

namespace FME\Events\Model\Event\EventList;

class EventToolbar
{

    const PAGE_PARM_NAME = 'p';
    const ORDER_PARAM_NAME = 'product_list_order';
    const DIRECTION_PARAM_NAME = 'product_list_dir';
    const MODE_PARAM_NAME = 'product_list_mode';
    const LIMIT_PARAM_NAME = 'product_list_limit';

    protected $request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function getEventOrder()
    {
        
        return $this->request->getParam(self::ORDER_PARAM_NAME);
    }

    public function getEventDirection()
    {
        return $this->request->getParam(self::DIRECTION_PARAM_NAME);
    }

    public function getEventMode()
    {
        return $this->request->getParam(self::MODE_PARAM_NAME);
    }

    public function getEventLimit()
    {
        return $this->request->getParam(self::LIMIT_PARAM_NAME);
    }

    public function getEventCurrentPage()
    {
        $page = (int) $this->request->getParam(self::PAGE_PARM_NAME);
        return $page ? $page : 1;
    }
}
