<?php

namespace FME\Events\Model;

class Products extends \Magento\Framework\Model\AbstractModel
{

        
    protected function _construct()
    {
        $this->_init('FME\Events\Model\ResourceModel\Products');
    }
}
