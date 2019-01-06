<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Plugins\Bundle\Block\Catalog\Product\View\Type;

class Bundle
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Xnotif\Helper\Data $helper
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
    }

    public function afterToHtml(\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $subject, $html)
    {
        $product = $subject->getProduct();

        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        $optionsIds = $typeInstance->getOptionsIds($product);
        if (!$optionsIds) {
            return $html;
        }

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $optionsIds,
            $product
        );

        $json = [];
        foreach ($selectionCollection as $item) {
            /*generate information only for out of stock items*/
            if ($item->getData('amasty_native_is_salable') == 0) {
                $json[$item->getId()] = [
                    'is_salable' => $item->getData('amasty_native_is_salable'),
                    'alert'      => $this->helper->getStockAlert($item)
                ];
            }
        }

        $json = $this->jsonEncoder->encode($json);
        $html = '<script>
                    window.amxnotif_json_config = ' . $json . '
                </script>'
            . $html;

        return $html;
    }
}
