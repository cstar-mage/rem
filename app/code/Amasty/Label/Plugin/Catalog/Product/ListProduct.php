<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Plugin\Catalog\Product;

class ListProduct
{
    /**
     * @var \Amasty\Label\Model\LabelViewer
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * ListProduct constructor.
     * @param \Amasty\Label\Model\LabelViewer $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Amasty\Label\Model\LabelViewer $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result
    ) {
        if (!$this->registry->registry('amlabel_category_observer')) {
            $products = $subject->getLoadedProductCollection();
            foreach ($products as $product) {
                $result .= $this->helper->renderProductLabel($product, 'category', true);
            }
        }

        return $result;
    }
}
