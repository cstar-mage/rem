<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model;

use MageWorx\MultiFees\Api\Data\CartFeeInterface;

class CartFee extends AbstractFee implements CartFeeInterface
{
    const RESOURCE_MODEL_CLASS = 'MageWorx\MultiFees\Model\ResourceModel\CartFeeResource';

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageworx_multifees_cart_fee';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $cacheTag = 'mageworx_multifees_cart_fee';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_multifees_cart_fee';

    /**
     * Fee constructor.
     *
     * @param Fee\Condition\CartFee\CombineFactory $condCombineFactory
     * @param \MageWorx\MultiFees\Helper\Fee $helperFee
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineFactory
     * @param ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \MageWorx\MultiFees\Model\Fee\Condition\CartFee\CombineFactory $condCombineFactory,
        \MageWorx\MultiFees\Helper\Fee $helperFee,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineFactory,
        \MageWorx\MultiFees\Model\ResourceModel\Option\CollectionFactory $feeOptionCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helperFee                  = $helperFee;
        $this->feeOptionCollectionFactory = $feeOptionCollectionFactory;
        $this->condCombineFactory         = $condCombineFactory;
        $this->condProdCombineFactory     = $condProdCombineFactory;
        $this->storeManager               = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }
}
