<?php

namespace FME\Events\Model;

class Media extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        $this->_init('FME\Events\Model\ResourceModel\Media');
    }
}
