<?php

namespace FME\Events\Model\ResourceModel;

class Media extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('fme_events_media', 'media_id');
    }
}
