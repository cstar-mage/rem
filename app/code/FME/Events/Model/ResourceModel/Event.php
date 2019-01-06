<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FME\Events\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Stdlib\DateTime;

/**
 * Cms page mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Event extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;
    protected $_tagCollectionFactory;
    protected $_tagGmapTable;
   
    

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fme_events', 'event_id');
    }
    protected function _beforeSave(AbstractModel $object)
    {

        if (!$this->getIsUniqueNewsToStores($object)) {
            throw new LocalizedException(
                __('An event identifier with the same properties already exists in the selected store.')
            );
        }
        return $this;
    }


    public function getIsUniqueNewsToStores(AbstractModel $object)
    {

        $select = $this->getConnection()->select()
        ->from(['cb' => $this->getTable('fme_events')])
        ->where('cb.event_url_prefix = ?', $object->getData('event_url_prefix'))
        ->where('cb.event_id NOT IN (?)', $object->getData('event_id'));
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    protected function _afterSave(AbstractModel $product)
    {
        $this->_saveEventMedia($product);
        $this->_saveEventProducts($product);
        $this->_saveEventStoreView($product);
        return parent::_afterSave($product);
    }
    protected function _saveEventMedia(\Magento\Framework\Model\AbstractModel $object)
    {
      
        $mediaIds = $object->getProduct();
        $mediaIds = $mediaIds['gallery']['images'];
     
        if (isset($mediaIds)) {
            $condition = $this->getConnection()->quoteInto('event_id = ?', $object->getId());
            $this->getConnection()->delete($this->getTable('fme_events_media'), $condition);
            foreach ($mediaIds as $media) {
                $gMediaArray = [];
                $gMediaArray['event_id']= $object->getId();
                $gMediaArray['label'] = $media['label'];
                $gMediaArray['file'] = rtrim($media['file'], ".tmp");
                $gMediaArray['position'] = $media['position'];
                if ($media['removed'] != '1') {
                    $this->getConnection()
                    ->insert($this->getTable('fme_events_media'), $gMediaArray);
                }
            }
        }
    }
    protected function _saveEventProducts(\Magento\Framework\Model\AbstractModel $object)
    {
            $relatedPids = $object->getData('entity_id');
           
        if (isset($relatedPids)) {
            $condition = $this->getConnection()->quoteInto(
                'event_id = ?',
                $object->getId()
            );
            $this->getConnection()->delete(

                $this->getTable('fme_events_products'),
                $condition
            );

            foreach ($relatedPids as $rPids) {
                $gProdArray = [];
                $gProdArray['event_id']  = $object->getId();
                $gProdArray['entity_id'] = $rPids;
                $this->getConnection()->insert($this->getTable('fme_events_products'), $gProdArray);
            }
        }
    }
    protected function _saveEventStoreView(\Magento\Framework\Model\AbstractModel $object)
    {
        
        $mediaIds = $object->getData('store_id');
        if (isset($mediaIds)) {
            $condition = $this->getConnection()->quoteInto('event_id = ?', $object->getId());
            $this->getConnection()->delete($this->getTable('fme_events_store'), $condition);
            foreach ($mediaIds as $media) {
                $gMediaArray = [];
                $gMediaArray['event_id']= $object->getId();
                $gMediaArray['store_id'] = $media;
                    $this->getConnection()
                    ->insert($this->getTable('fme_events_store'), $gMediaArray);
            }
        }
    }
   
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }
}
