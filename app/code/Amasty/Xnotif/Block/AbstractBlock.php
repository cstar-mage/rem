<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;

class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resource,
        Session $customerSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->configurableModel = $configurableModel;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->setTemplate('subscription.phtml');
        $this->loadCollection();
    }

    private function loadCollection()
    {
        $type = $this->getType();
        $alertTable = $this->resource->getTableName('product_alert_' . $type);
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name');

        $select = $collection->getSelect();
        $select->joinInner(
            ['s' => $alertTable],
            's.product_id = e.entity_id',
            ['add_date', 'alert_' . $type . '_id', 'parent_id']
        )
            ->where('s.status=0')
            ->where(
                'customer_id=? OR email=?',
                $this->customerSession->getCustomerId(),
                $this->customerSession->getCustomer()->getEmail()
            )
            ->group(['s.product_id']);

        $collection->setFlag('has_stock_status_filter', true);

        $this->setSubscriptions($collection);
    }

    public function getRemoveUrl($order)
    {
        $type = $this->getType();
        $id = $order->getData('alert_' . $type . '_id');

        return $this->getUrl(
            'xnotif/' . $type . '/remove',
            ['item' => $id]
        );
    }

    public function getProductUrl($_order)
    {
        $product = $this->getProduct($_order->getEntityId());
        $url = $product->getUrlModel()->getUrl($product);

        return $url;
    }

    public function getSupperAttributesByChildId($id)
    {
        $parentIds = $this->configurableModel->getParentIdsByChild($id);
        $attributes = [];
        if (!empty($parentIds)) {
            $product = $this->getProductById($parentIds[0]);
            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        }
        
        return $attributes;
    }

    private function getUrlHash($product)
    {
        $attributes = $this->getSupperAttributesByChildId($product->getId());
        $hash = '';
        
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $attributeCode = $attribute->getData('product_attribute')->getData('attribute_code');
                $value = $product->getData($attributeCode);
                $hash .= '&' . $attributeCode . "=" . $value;
            }

            $hash = '#' . substr($hash, 1);//remove first &
        }
        
        return $hash;
    }

    public function getUrlProduct($product)
    {
        $parentIds = $this->configurableModel->getParentIdsByChild($product->getId());
        $hash = $this->getUrlHash($product);
        
        if (!empty($parentIds) && isset($parentIds[0])) {
            $product = $this->getProductById($parentIds[0]);
        }

        $baseUrl = $product->getUrlModel()->getUrl($product);
        $url = $baseUrl . $hash;
        
        return $url;
    }

    public function getProduct($id)
    {
        $product = $this->getProductById($id);
        
        return $product;
    }
    
    public function getImageSrc($product)
    {
        if ($this->_scopeConfig->isSetFlag('amxnotif/general/account_image')) {
            $parentIds = $this->configurableModel->getParentIdsByChild($product->getId());
            if (isset($parentIds[0])) {
                $product = $this->getProductById($parentIds[0]);
            }
        }
        
        $image = $this->imageHelper->init($product, 'small_image')
            ->setImageFile($product->getImage())
            ->resize(45)
            ->getUrl();
        
        return $image;
    }
    
    private function getProductById($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $exception) {
            $product = null;
        }
        
        return $product;
    }

    public function getConfirmationText()
    {
        //sniffer think that it is sql query
        $text = 'Are you sure you would like' . ' to remove this item from the subscriptions?';

        return __($text);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
