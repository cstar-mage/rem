<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Plugin\Catalog\Model;

use Magento\Catalog\Model\Product as ProductModel;

class Product
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Product constructor.
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->urlInterface = $urlInterface;
        $this->request = $request;
    }

    /**
     * Redirect to quickview controller if something wrong
     * @param ProductModel $subject
     * @param \Closure $proceed
     * @param null $useSid
     * @return mixed|null|string
     */
    public function aroundGetProductUrl(
        ProductModel $subject,
        \Closure $proceed,
        $useSid = null
    ) {
        if ($this->request->getParam('quickview_url')) {
            $result = $this->urlInterface->getUrl('amasty_quickview/ajax/view', ['id' => $subject->getId()]);
        } else {
            $result = $proceed($useSid);
        }

        return $result;
    }
}
