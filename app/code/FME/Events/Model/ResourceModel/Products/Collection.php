<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\Events\Model\ResourceModel\Products;

use \FME\Events\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_previewFlag;
    
    protected function _construct()
    {
        $this->_init(
            
            'FME\Events\Model\Products',
            'FME\Events\Model\ResourceModel\Products'
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
