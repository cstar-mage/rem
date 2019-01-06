<?php

namespace Remedy\PromotedBrandProducts\Block;


class Promotional extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;
   
   
    protected $_config;
    
    protected $_product;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Eav\Model\Config $Config,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $CollectionFactory,
        \Magento\Catalog\Model\Product $Product,
        array $data = []    
    )
    {
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        $this->_config = $Config;
        $this->_collectionFactory = $CollectionFactory;
        $this->_product = $Product;
        parent::__construct($context, $data);
    }
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }
    public function getAttributeValue($name){
		$result = '';
		$attribute = $this->_config->getAttribute('catalog_product', 'manufacturer');
		$options = $attribute->getSource()->getAllOptions();
		foreach($options as $key => $val){
			if($name === $val['label']){
					$result = $val['value'];
					break;
			}
		}
		return $result;
	}
    public function getCollection($name)
    {
		$attributeValue = $this->getAttributeValue($name);
		$collectionProd = $this->_collectionFactory->create();
		 $collectionProd->addAttributeToSelect('*')
            ->addAttributeToFilter(
            array(
                array('attribute'=>'manufacturer','eq'=>$attributeValue)
            )
        )
        ->addAttributeToFilter('status', array('eq' => 1))
        ->addAttributeToFilter('promote', array('eq' => 1));
        return $collectionProd;
    }
    
    public function getCount($name){
		$attributeValue = $this->getAttributeValue($name);
		$collectionProd = $this->_collectionFactory->create();
		 $collectionProd->addAttributeToSelect('*')
            ->addAttributeToFilter(
            array(
                array('attribute'=>'manufacturer','eq'=>$attributeValue)
            )
        )
        ->addAttributeToFilter('status', array('eq' => 1));	
        $result = count($collectionProd->getData());
        return $result;
	}
	public function getAbstractProductObj(){
			$blockObj= $this->getLayout()->createBlock('\Magento\Catalog\Block\Product\AbstractProduct');
			return $blockObj;
	}	
	
	public function getProductObj($productId){
			$productObj = $this->_product->load($productId);
			return $productObj;
	}


}
