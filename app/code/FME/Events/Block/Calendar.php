<?php
namespace FME\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;

class Calendar extends Template
{
         
    protected $scopeConfig;
    protected $date;
    protected $collectionFactory;
    protected $mediaFactory;
    protected $objectManager;
    protected $_calendarEvents;
        
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FME\Events\Model\Event $calendarEvents,
        \FME\Events\Model\ResourceModel\Event\
        CollectionFactory $collectionFactory,
        \FME\Events\Model\ResourceModel\Media\
        CollectionFactory $mediaFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        ObjectManagerInterface $objectManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->mediaFactory = $mediaFactory;
        $this->objectManager = $objectManager;
        $this->date = $date;
        $this->_calendarEvents = $calendarEvents;
                
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
                   
        $this->pageConfig->getTitle()->set("Events Calendar");

        return parent::_prepareLayout();
    }
    

    public function getEventsCoolectCalendar()
    {
        return $this->_calendarEvents->getCalnedarPopupValues();
    }

    /**
     * @return string
     */
    
    public function getMediaUrl()
    {

            $media_dir = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            return $media_dir;
    }

    public function getCurrDateTime()
    {
      $datewithoffset = $this->date->date();
          
     return $datewithoffset;
     
    }
}
