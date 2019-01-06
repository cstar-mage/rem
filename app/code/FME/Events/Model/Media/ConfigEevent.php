<?php

namespace FME\Events\Model\Media;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Store\Model\StoreManagerInterface;

class ConfigEevent implements EventConfigInterface
{

    protected $storeManager;
    private $attributeHelper;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }
    
    public function getEventBaseMediaPathAddition()
    {
        return 'events/event/media';
    }

    public function getEventBaseMediaUrlAddition()
    {
        return 'events/event/media';
    }

    public function getEventBaseMediaPath()
    {
        return 'events/event/';
    }

    public function getEventBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'tmp/events/event/media';
    }

    public function getEventBaseTmpMediaPath()
    {
        return 'tmp/' . $this->getEventBaseMediaPathAddition();
    }

    public function getEventBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'tmp/' . $this->getEventBaseMediaUrlAddition();
    }

    public function getEventMediaUrl($file)
    {
        return $this->getEventBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    public function getEventMediaPath($file)
    {
        return $this->getEventBaseMediaPath() . '/' . $this->_prepareFile($file);
    }

    public function getEventTmpMediaUrl($file)
    {
        return $this->getEventBaseTmpMediaUrl() . '/' . $this->_prepareFile($file);
    }

    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
