<?php

namespace FME\Events\Model;

class Event extends \Magento\Framework\Model\AbstractModel
{
        const STATUS_ENABLED = 1;
        const STATUS_DISABLED = 0;
    protected $_logger;

    protected function _construct()
    {
        $this->_init('FME\Events\Model\ResourceModel\Event');
    }

    public function getAvailableStatuses()
    {
        $availableOptions = ['0' => 'Disable',
                           '1' => 'Enable'];
        return $availableOptions;
    }

    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('products_position');
    

        if ($array === null) {
            $temp = $this->getData('event_id');
            $tagsname = $this->getRelatedProducts($temp);

            for ($i = 0; $i < sizeof($tagsname); $i++) {
                $array[$tagsname[$i]] = 0;
            }
        
            $this->setData('products_position', $array);
        }
        return $array;
    }
    
    public function getRelatedProducts($id)
    {

        $select = $this->_getResource()->getConnection()->select()->from($this->_getResource()->getTable('fme_events_products'))->where('event_id = ?', $id);
        $data = $this->_getResource()->getConnection()->fetchAll($select);
        
        if ($data) {
            $productsArr = [];
            foreach ($data as $_i) {
                $productsArr[] = $_i['entity_id'];
            }
           
            return $productsArr;
        }
    }
    
    public function getStores($id)
    {
        $select = $this->_getResource()->getConnection()->select()->from($this->_getResource()->getTable('fme_events'))->where('event_id = ?', $id);
        $data = $this->_getResource()->getConnection()->fetchAll($select);
        if ($data) {
            return $data;
        }
    }

    public function getCalnedarPopupValues()
    {
        $select = $this->_getResource()->getConnection()->select()->from($this->_getResource()->getTable('fme_events'), ['id'=>'event_id','color','event_venue','is_recurring','event_url_prefix','event_meta_description','recurring_by','title'=>'event_name','start'=>'event_start_date','end'=>'event_end_date','dow'=>'recurring_intervals'])
        ->where('is_active = ?', '1');
        $data = $this->_getResource()->getConnection()
          ->fetchAll($select);
          return $data;
    }

    public function getRecurrStatuses()
    {
        $availableOptions = [   '0' => '--Seclect--',
                                '1' => 'Weekly',
                                '2' => 'Monthly',
                                '3' => 'Yearly',
                                
                           ];
        return $availableOptions;
    }
    
    public function getEventColors()
    {
        $availableOptions = [   '0'         => '--Seclect--',
                                'red'       => 'Red',
                                'blue'      => 'Blue',
                                'green'     => 'Green',
                                'purple'    => 'Purple',
                                'orange'    => 'Orange',
                                'yellow'    => 'Yellow',
                                'black'     => 'Black',
                                'brown'     => 'Brown',
                                'pink'      => 'Pink'
                           ];
        return $availableOptions;
    }
    
    public function getRecurrIntervals()
    {
        $availableOptions = [   '0' => '--Seclect--',
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '5' => '5',
                                '6' => '6',
                                '7' => '7'                            ];
        return $availableOptions;
    }
}
