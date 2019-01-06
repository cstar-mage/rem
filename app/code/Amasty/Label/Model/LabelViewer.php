<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Labels;
use Amasty\Label\Model\ResourceModel\Labels\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\Profiler;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class LabelViewer
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Configurable
     */
    private $productTypeConfigurable;

    /**
     * @var CollectionFactory
     */
    private $labelCollectionFactory;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Label\Model\ResourceModel\Index
     */
    private $labelIndex;

    /**
     * @var \Amasty\Label\Helper\Config
     */
    private $config;

    /**
     * LabelViewer constructor.
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param Configurable $catalogProductTypeConfigurable
     * @param CollectionFactory $labelCollectionFactory
     * @param \Amasty\Base\Model\Serializer $serializer
     * @param Session $customerSession
     * @param ResourceModel\Index $labelIndex
     * @param \Amasty\Label\Helper\Config $config
     */
    public function __construct(
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Configurable $catalogProductTypeConfigurable,
        CollectionFactory $labelCollectionFactory,
        \Amasty\Base\Model\Serializer $serializer,
        Session $customerSession,
        \Amasty\Label\Model\ResourceModel\Index $labelIndex,
        \Amasty\Label\Helper\Config $config
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->serializer = $serializer;
        $this->customerSession = $customerSession;
        $this->labelIndex = $labelIndex;
        $this->config = $config;
    }

    /**
     * @param Product $product
     * @param string $mode
     * @param bool $shouldMove
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function renderProductLabel(Product $product, $mode = 'category', $shouldMove = false)
    {
        if (!$this->config->isUseIndex()) {
            return $this->renderProductLabelWithoutIndex($product, $mode, $shouldMove);
        }

        $html = '';
        $appliedLabelIds = [];
        $applied = false;

        Profiler::start('__RenderAmastyProductLabel__');
        foreach ($this->getCollection($product->getId(), $product->getStoreId()) as $label) {
            if ($this->validateNonProductDependConditions($label, $applied)) {
                continue;
            }

            $applied = true;
            $label->setShouldMove($shouldMove);
            $label->init($product, $mode);

            $html .= $this->generateHtml($label);
            $appliedLabelIds[] = $label->getId();
        }

        /* apply label from child products*/
        if (in_array($product->getTypeId(), [Grouped::TYPE_CODE, Configurable::TYPE_CODE])
            && $this->isLabelForParentEnabled($product->getStoreId())
        ) {
            $usedProds = $this->getUsedProducts($product);
            foreach ($usedProds as $child) {
                foreach ($this->getCollection($child->getId(), $child->getStoreId()) as $label) {
                    /** @var Labels $label */
                    if (!$label->getUseForParent()
                        || $this->validateNonProductDependConditions($label, $applied)
                    ) {
                        continue;
                    }
                    /* apply label only one time(remove duplicated for child products */
                    if (in_array($label->getId(), $appliedLabelIds)) {
                        continue;
                    }

                    $applied = true;
                    $label->setShouldMove($shouldMove);
                    $label->init($child, $mode, $product);

                    $html .= $this->generateHtml($label);
                    $appliedLabelIds[] = $label->getId();
                }
            }
        }
        Profiler::stop('__RenderAmastyProductLabel__');

        return $html;
    }

    /**
     * @param \Amasty\Label\Model\Labels $label
     * @param bool $applied
     * @return bool
     */
    private function validateNonProductDependConditions(Labels $label, &$applied)
    {
        if ($label->getIsSingle() === '1' && $applied) {
            return true;
        }

        // need this condition, because in_array returns true for NOT LOGGED IN customers
        if ($label->getCustomerGroupEnabled()
            && !$this->checkCustomerGroupCondition($label)
        ) {
            return true;
        }

        if (!$label->checkDateRange()) {
            return true;
        }

        return false;
    }

    /**
     * if anyone label has setting - UseForParent - check all
     * @param int $storeId
     * @return bool
     */
    private function isLabelForParentEnabled($storeId)
    {
        $collection = $this->labelCollectionFactory->create()
            ->addActiveFilter()
            ->addFieldToFilter('stores', ['like' => "%$storeId%"])
            ->addFieldToFilter(LabelInterface::USE_FOR_PARENT, 1);

        return $collection->getSize() ? true : false;
    }

    /**
     * @param Labels $label
     * @return bool
     */
    private function checkCustomerGroupCondition(Labels $label)
    {
        $groups = $label->getData('customer_group_ids');
        if ($groups === '') {
            return true;
        }
        $groups = $this->serializer->unserialize($groups);

        return in_array(
            (int)$this->customerSession->getCustomerGroupId(),
            $groups
        );
    }

    /*
     * generate block with label configuration
     * @param \Amasty\Label\Model\Labels $label
     * @return string
     */
    private function generateHtml(Labels $label)
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            \Amasty\Label\Block\Label::class,
            'amasty.label',
            ['data' => ['label' => $label]]
        );
        $html = $block->setLabel($label)->toHtml();

        return $html;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return $mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCollection($productId, $storeId)
    {
        $labelIds = $this->labelIndex->getIdsFromIndex($productId, $storeId);
        if (!count($labelIds)) {
            return [];
        }

        $collection = $this->labelCollectionFactory->create()
            ->addActiveFilter()
            ->addFieldToFilter(LabelInterface::LABEL_ID, $labelIds)
            ->setOrder('pos', 'asc');

        return $collection;
    }

    /**
     * @param Product $product
     * @return array|\Magento\Catalog\Api\Data\ProductInterface[]
     */
    private function getUsedProducts(Product $product)
    {
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            return $this->productTypeConfigurable->getUsedProducts($product);
        } else { // product is grouped
            return $product->getTypeInstance(true)->getAssociatedProducts($product);
        }
    }

    /**
     * @param Product $product
     * @param string $mode
     * @param bool $shouldMove
     * @return string
     */
    public function renderProductLabelWithoutIndex(Product $product, $mode = 'category', $shouldMove = false)
    {
        $html = '';
        $appliedLabelIds = [];
        $applied = false;

        Profiler::start('__RenderAmastyProductLabel__');
        foreach ($this->getNonIndexCollection($product->getStoreId()) as $label) {
            /** @var Labels $label */
            if ($this->validateNonProductDependConditions($label, $applied)) {
                continue;
            }

            $label->setShouldMove($shouldMove);
            $label->init($product, $mode);

            if ($label->isApplicable()) {
                $applied = true;
                $appliedLabelIds[] = $label->getId();
                $html .= $this->generateHtml($label);
            } elseif ($label->getUseForParent()
                && ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped')
            ) {
                $usedProds = $this->getUsedProducts($product);
                foreach ($usedProds as $child) {
                    $label->init($child, $mode, $product);
                    if ($label->isApplicable()) {
                        $applied = true;
                        $html .= $this->generateHtml($label);
                        break;
                    }
                }
            }
        }
        Profiler::stop('__RenderAmastyProductLabel__');

        return $html;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    private function getNonIndexCollection($storeId)
    {
        $collection = $this->labelCollectionFactory->create()
            ->addActiveFilter()
            ->addFieldToFilter('stores', ['like' => "%$storeId%"])
            ->setOrder('pos', 'asc');

        return $collection;
    }
}
