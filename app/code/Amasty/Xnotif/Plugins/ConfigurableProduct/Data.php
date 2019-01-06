<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ConfigurableProduct;

class Data extends \Magento\ConfigurableProduct\Helper\Data
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockStateProvider;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Amasty\Xnotif\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->imageHelper = $imageHelper;
        $this->moduleManager = $moduleManager;
        parent::__construct($imageHelper);
        $this->stockRegistry = $stockRegistry;
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $aStockStatus = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            $key = [];
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;

                /*Amasty code start - code here for improving performance*/
                $key[] = $product->getData(
                    $attribute->getData('product_attribute')->getData(
                        'attribute_code'
                    )
                );
            }

            if ($key && !$this->moduleManager->isEnabled('Amasty_Stockstatus')) {
                $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
                $saleable =  $stockItem->getIsInStock() && $this->helper->verifyStock($stockItem);

                $stockStatus = (!$saleable)? __('Out of Stock'): '';
                $aStockStatus[implode(',', $key)] = [
                    'is_in_stock'   => $saleable,
                    'custom_status' => $stockStatus,
                    'product_id'    => $product->getId()
                ];
                if (!$saleable) {
                    $aStockStatus[implode(',', $key)]['stockalert'] =
                        $this->helper->getStockAlert($product);
                }
            }
            /*Amasty code end*/
        }

        $this->registry->unregister('amasty_xnotif_data');
        $this->registry->register('amasty_xnotif_data', $aStockStatus);

        return $options;
    }
}
