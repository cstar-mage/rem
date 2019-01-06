<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Quickview
 */


namespace Amasty\Quickview\Plugin\Framework\HTTP\PhpEnvironment;

use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\Registry as Registry;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class Response
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * Response constructor.
     * @param Request $request
     * @param Registry $coreRegistry
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        Request $request,
        Registry $coreRegistry,
        UrlHelper $urlHelper
    ) {
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param \Magento\Framework\HTTP\PhpEnvironment\Response $subject
     * @param string $value
     * @return string mixed
     */
    public function beforeAppendBody(
        \Magento\Framework\HTTP\PhpEnvironment\Response $subject,
        $value
    ) {
        if ($this->request->getModuleName() == 'amasty_quickview') {
            $product = $this->coreRegistry->registry('current_product');

            if ($product && $product->getId()) {
                $currentUenc = $this->urlHelper->getEncodedUrl();
                $refererUrl = $product->getProductUrl();
                $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
                $value = str_replace($currentUenc, $newUenc, $value);
            }
        }

        return [$value];
    }
}
