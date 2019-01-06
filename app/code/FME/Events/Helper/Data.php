<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FME\Events\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;

class Data extends AbstractHelper
{
    protected $_timezoneInterface;
    const XML_PATH_ENABLED                      =   'events/basic_configs/event_mod_enable';
    const HEADER_LINK_TITLE                      =   'events/basic_configs/header_link';
    const XML_EVENT_HEADER_LINK_ENABLE           =   'events/basic_configs/header_link_enable';
    const XML_EVENT_BOTTOM_LINK_ENABLE           =   'events/basic_configs/bottom_link_enable';
    const BOTTOM_LINK_TITLE                      =   'events/basic_configs/bottom_link';
    const XML_EVENT_PAGE_METAKEYWORD              =   'events/seo_info/meta_keywords';
    const XML_EVENT_PAGE_METADESCRIPTION          =   'events/seo_info/meta_description';
    const XML_EVENT_STANDARD_LATITUDE             =   'events/basic_configs/std_latitude';
    const XML_EVENT_STANDARD_LONGITUDE            =   'events/basic_configs/std_longitude';
    const XML_GMAP_API_KEY                       =   'events/basic_configs/api_key';
    const XML_GMAP_HEADERLINK_TEXT               =   'events/seo_info/page_title';
    const EVENT_META_DESCRIPTION                 =   'events/seo_info/meta_description';
    const EVENT_META_KEYWORDS                    =   'events/seo_info/meta_keywords';
    const EXT_IDENTIFIER                         =   'events/seo_info/events_url_prefix';
    const XML_EVENT_SEO_SUFFIX                         =   'events/seo_info/events_url_suffix';
    const LANDING_LAYOUT                         =   'events/events_pages_layouts/landing_layout';
    const EVENT_VIEW_LAYOUT                      =   'events/events_pages_layouts/events_view_layout';
    const CALENDAR_LAYOUT                        =   'events/events_pages_layouts/events_calendar_layout';
    const EXPIRED_EVENT                          =   'events/event_status_notifications/expired_event';
    const ERR_EMPTY_COLLECTION                   =   'events/event_status_notifications/err_empty_collection';
    const XML_EVENT_SEO_IDENTIFIER                =   'events/calendar_configs/all_events_link';
    

        public function __construct(
             \Magento\Framework\App\Helper\Context $context,         
             \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) 
    {
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }
    
    public function getTimeAccordingToTimeZone($dateTime)
    {
        
        $today = $this->_timezoneInterface->date()->format('m/d/y H:i:s');    
        $dateTimeAsTimeZone = $this->_timezoneInterface
                                        ->date(new \DateTime($dateTime))
                                        ->format('m/d/y H:i:s');
        return $dateTimeAsTimeZone;
    }

    public function getJsUrl($url)
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).'event/'.$url;
    }
    public function isEnabledInFrontend()
    {
         $isEnabled = true;
         $enabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
         return $isEnabled;
    }
    public function getEventPageTitle()
    {
        
        return $this->scopeConfig->getValue(self::XML_GMAP_HEADERLINK_TEXT, ScopeInterface::SCOPE_STORE);
    }
    public function getEventEmptyCollection()
    {
        
        return $this->scopeConfig->getValue(self::ERR_EMPTY_COLLECTION, ScopeInterface::SCOPE_STORE);
    }
    public function getEventMetaKeywords()
    {

        return $this->scopeConfig->getValue(self::XML_EVENT_PAGE_METAKEYWORD, ScopeInterface::SCOPE_STORE);
    }
    public function getEventMetadescription()
    {
        
        return $this->scopeConfig->getValue(self::XML_EVENT_PAGE_METADESCRIPTION, ScopeInterface::SCOPE_STORE);
    }
    public function getEventStandardLatitude()
    {
        
        return $this->scopeConfig->getValue(self::XML_EVENT_STANDARD_LATITUDE, ScopeInterface::SCOPE_STORE);
    }
    public function getEventStandardLongitude()
    {
        
        return $this->scopeConfig->getValue(self::XML_EVENT_STANDARD_LONGITUDE, ScopeInterface::SCOPE_STORE);
    }
    
    public function getGMapAPIKey()
    {
        return $this->scopeConfig->getValue(self::XML_GMAP_API_KEY, ScopeInterface::SCOPE_STORE);
    }
    public function isHeaderLinkEnable()
    {
        
        return $this->scopeConfig->getValue(self::XML_EVENT_HEADER_LINK_ENABLE, ScopeInterface::SCOPE_STORE);
    }
    public function getHeaderLinkLabel()
    {
        if (self::isHeaderLinkEnable()) {
            return $this->scopeConfig->getValue(self::HEADER_LINK_TITLE, ScopeInterface::SCOPE_STORE);
        }
    }
    public function isFooterLinkEnable()
    {
        return $this->scopeConfig->getValue(self::XML_EVENT_BOTTOM_LINK_ENABLE, ScopeInterface::SCOPE_STORE);
    }
    public function getFooterLinkLabel()
    {
        if (self::isFooterLinkEnable()) {
            return $this->scopeConfig->getValue(self::BOTTOM_LINK_TITLE, ScopeInterface::SCOPE_STORE);
        }
    }
    public function getEventSeoSuffix()
    {
        
        return $this->scopeConfig->getValue(self::XML_EVENT_SEO_SUFFIX, ScopeInterface::SCOPE_STORE);
    }
    public function getEventSeoIdentifier()
    {
            return $this->scopeConfig->getValue(self::EXT_IDENTIFIER, ScopeInterface::SCOPE_STORE);
    }
    
    public function getEventFinalIdentifier()
    {
        if ($this->getEventSeoIdentifier()) {
            return $this->getEventSeoIdentifier().$this->getEventSeoSuffix();
        } else {
            return 'event';
        }
    }
    
    public function getEventFinalDetailIdentifier($detailId)
    {
        if ($this->getEventSeoIdentifier()) {
            return $this->getEventSeoIdentifier().'/'.$detailId.$this->getEventSeoSuffix();
        } else {
            return 'event/'.$detailId.$this->getEventSeoSuffix();
        }
    }
    
    
    public function getEventLink()
    {
        $identifier = $this->getEventSeoIdentifier();
        $seo_suffix = $this->getEventSeoSuffix();
        if (isset($identifier) && isset($seo_suffix)) {
            return $identifier.$seo_suffix;
        } else {
            return 'event';
        }
    }
    public function getGMapZoom()
    {
        if (self::XML_GMAP_ZOOM =='') {
            return 8;
        }
        return $this->scopeConfig->getValue(self::XML_GMAP_ZOOM, ScopeInterface::SCOPE_STORE);
    }
}
