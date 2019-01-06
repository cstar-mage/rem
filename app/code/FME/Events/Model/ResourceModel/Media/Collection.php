<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\Events\Model\ResourceModel\Media;

use \FME\Events\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'media_id';
    protected $_previewFlag;
    
    protected function _construct()
    {
        $this->_init(
            
            'FME\Events\Model\Media',
            'FME\Events\Model\ResourceModel\Media'
        );
        $this->_map['fields']['media_id'] = 'main_table.media_id';
    }
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
