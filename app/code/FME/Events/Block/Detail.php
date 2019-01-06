<?php
namespace FME\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Block\Product\Options;
use Magento\Catalog\Block\Product\AbstractProduct;

class Detail extends Template
{

    protected $collectionFactory;
    protected $mediaFactory;
    protected $prodFactory;
    protected $objectManager;
    protected $productFactory;
    public $eventsHelper;
    protected $productTypeConfig;
    protected $_registry = null;
    protected $productRepository;
    protected $_productCollectionFactory;
    protected $listProductBlock;
    protected $viewProductBlock;
    protected $optionsProductBlock;
    protected $absHelper;
    protected $_cartHelper;
    protected $_selecto;

    public function __construct(
        ProductFactory $productFactory,
        \FME\Events\Model\ResourceModel\Event\CollectionFactory $collectionFactory,
        \FME\Events\Model\ResourceModel\Media\CollectionFactory $mediaFactory,
        \FME\Events\Model\ResourceModel\Products\CollectionFactory $prodFactory,
        \FME\Events\Helper\Data $helper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Registry $coreRegistry,
        ProductRepositoryInterface $productRepository,
        ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Magento\Catalog\Block\Product\View $viewProductBlock,
        \Magento\Catalog\Block\Product\View\Options $optionsProductBlock,
        \Magento\Catalog\Block\Product\AbstractProduct $absHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->mediaFactory = $mediaFactory;
        $this->prodFactory = $prodFactory;
        $this->objectManager = $objectManager;
        $this->eventsHelper = $helper;
        $this->productTypeConfig = $productTypeConfig;
        $this->_registry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->listProductBlock = $listProductBlock;
        $this->viewProductBlock = $viewProductBlock;
        $this->optionsProductBlock = $optionsProductBlock;
        $this->absHelper = $absHelper;
        $this->_cartHelper = $cartHelper;

         parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        $params = $this->getRequest()->getParams();
        $prefix = $params['id'];
        
        if ($this->eventsHelper->isEnabledInFrontend()) {
             $this->pageConfig->getTitle()->set($this->getEventDetailTitle($prefix, 'event_name'));
             $this->pageConfig->setKeywords($this->getEventDetailTitle($prefix, 'event_meta_keywords'));
             $this->pageConfig->setDescription($this->getEventDetailTitle($prefix, 'event_meta_description'));
  
            return parent::_prepareLayout();
        }
    }

    public function getEventAssocProducts($eid)
    {
            $eProducts = $this->prodFactory->create()->addFieldToFilter('event_id', $eid);

        if ($eProducts->getData()) {
            foreach ($eProducts as $evtProducts) {
                $evArray [] = $evtProducts->getEntityId();
            }
            return $evArray;
        }
    }

    public function getProductCollection($pid)
    {
        $collection = $this->_productCollectionFactory->create()
        ->addFieldToFilter('entity_id', $pid)
        ->addAttributeToFilter('visibility', array('nin' => array('1')))
        ->addAttributeToSelect('*')->load();
        return $collection;
    }

    public function getAddToCartPostParams($product)
    {
        return $this->listProductBlock->getAddToCartPostParams($product);
    }

    public function getProductPrice($product)
    {
        return $this->listProductBlock->getProductPrice($product);
    }

    public function getSubmitUrl($product)
    {
         $additional=[];
        return $this->absHelper->getSubmitUrl($product, $additional);
    }

    public function getAddToCartUrl($product)
    {
        $additional=[];
        return $this->_cartHelper->getAddUrl($product, $additional);
    }
    
    public function configVal($p)
    {
        $configValue = $this->getProduct($p)->getPreconfiguredValues()
            ->getData();
    }

    public function loadMyProduct($id)
    {
        if ($id) {
            $_product = $this->productFactory->create()->load($id);
            return $_product;
        }
    }

    public function getEventDetail($eventId)
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('event_url_prefix', $eventId);
        return $collection;
    }

    public function getEventDetailTitle($eventId, $earg)
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('event_url_prefix', $eventId);
        $collection = $collection->getData();
        $collection = $collection[0][$earg];
            return $collection;
    }

    public function getFrontEvents()
    {
        $toolbar = $this->getToolbarBlock();
        $order = $toolbar->getCurrentOrder();
        $dire  = $toolbar->getCurrentDirection();
          
        $collection = $this->collectionFactory->create()->addFieldToFilter('is_active', 1)
        ->setOrder($order, $dire);
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest(
                
        )->getParam('limit') : 5;
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

    public function getEventGalleries($eid)
    {
        $image = $this->mediaFactory->create()->         addFieldToFilter('event_id', $eid);
        return $image;
    }

    public function getMediaUrl()
    {

        $media_dir = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            return $media_dir.'tmp/skin01.zip';
    }
    
    public function getCarousUrl()
    {

        $media_dir = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            return $media_dir;
    }

    public function getJsUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    public function getEvento()
    {
        return $collection = $this->collectionFactory->create()->addFieldToFilter('is_active', 1);
    }
}
