<?php

namespace FME\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;  
class Event extends Template
{
    public $eventsHelper;
    protected $date;
    protected $timezone;
    public $_storeManager;
    protected $scopeConfig;
    protected $collectionFactory;
    protected $mediaFactory;
    protected $objectManager;
    protected $_defaultToolbarBlock = 'FME\Events\Block\EventToolbar';
        
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FME\Events\Model\ResourceModel\Event\
        CollectionFactory $collectionFactory,
        \FME\Events\Model\ResourceModel\Media\
        CollectionFactory $mediaFactory,
        \FME\Events\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->mediaFactory = $mediaFactory;
        $this->objectManager = $objectManager;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->_storeManager = $context->getStoreManager();
        $this->eventsHelper = $helper;
                
        parent::__construct($context,$data);
    }
        
    public function _prepareLayout()
    {
        if ($this->eventsHelper->isEnabledInFrontend()) {
            $this->pageConfig->setKeywords($this->eventsHelper->getEventMetaKeywords());
            $this->pageConfig->setDescription($this->eventsHelper->getEventMetadescription());
            $this->pageConfig->getTitle()->set($this->eventsHelper->getEventPageTitle());
            if ($this->getFrontEvents()) {
                $pager = $this->getLayout()->createBlock(
                    'Magento\Theme\Block\Html\Pager',
                    'fme.events.pager'
                )->setAvailableLimit([5=>5,10=>10,15=>15])->setShowPerPage(true)->setCollection(
                    $this->getFrontEvents()
                );
                $this->setChild('pager', $pager);
                $this->getFrontEvents()->load();
            }
            return parent::_prepareLayout();
        }
    }

    public function getMode()
    {
        return $this->getChildBlock('eventtoolbar')->getCurrentModeEvent();
    }
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
      
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock);
        return $block;
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('eventtoolbar');
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        $collection = $this->getFrontEvents();
        $orders = $this->getAvailableOrdersEvent();
        
        if ($orders) {
            $toolbar->setAvailableOrdersEvent($orders);
        }
        $sort = $this->getSortBy();

        if ($sort) {
            $toolbar->setDefaultOrderEvent($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirectionEvent($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModesEvent($modes);
        }
        
        $toolbar->setCollectionEvent($collection);

        $this->setChild('eventtoolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->getFrontEvents()]
        );

        $this->getFrontEvents()->load();

        return parent::_beforeToHtml();
    }
    public function getPagerHtml()
    {
         return $this->getChildHtml('pager');
    }

    public function getCurrDateTime()
    {
      $datewithoffset = $this->timezone->date();
      $datewithoutoffset = $this->date->gmtDate();     
     return $datewithoffset;
     
    }

    public function getFrontEvents()
    {
        $date =  (array)$this->timezone->date();
        $date = substr($date['date'],0,19);
        $storeId = $this->_storeManager->getStore()->getId();
        $toolbar = $this->getToolbarBlock();
        $order = $toolbar->getCurrentOrderEvent();
        $dire  = $toolbar->getCurrentDirectionEvent();
                  
        $collection = $this->collectionFactory->create()
        ->addStoreFilter($storeId)
        ->addFieldToFilter('is_active', 1)
        ->addFieldToFilter('event_end_date', array('gteq' => $date))
        ->setOrder($order, $dire);
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest(
                
        )->getParam('limit') : 5;
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    public function getCurrentImage($eid)
    {
        $image = $this->mediaFactory->create()->addFieldToFilter('event_id', $eid)
        ->setPageSize(1);
        $image = $image->getData();
        if ($image) {
            $image = $image['0']['file'];
        }

        return $image;
    }

    public function getMediaUrl()
    {
        $media_dir = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $media_dir;
    }
}
