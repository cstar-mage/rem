<?php
/**
 * Copyright 2018 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection as BrandCollection;

/**
 * Class Categories
 * @package Aheadworks\Blog\Model\Source
 */
class Brands implements OptionSourceInterface
{
    /**
     * @var \Aheadworks\Blog\Model\ResourceModel\Category\Collection
     */
    private $categoryCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory, BrandCollection $brandCollection)
    {
        $this->categoryCollection = $categoryCollectionFactory->create();
        $this->brandCollection = $brandCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
		$data = array();
		$i = 0;
		foreach($this->brandCollection as $brand){
			$data[$i]['value'] = $brand->getValue();
			$data[$i]['label'] = $brand->getTitle();
			$i++;
		}
		//~ print_r($data);
		//~ die;
		return $data;
		//~ die;
        //~ if (!$this->options) {
            //~ $this->categoryCollection->setOrder('sort_order', 'ASC');
            //~ $this->options = $this->categoryCollection->toOptionArray();
        //~ }
        //~ return $this->options;
    }
}
