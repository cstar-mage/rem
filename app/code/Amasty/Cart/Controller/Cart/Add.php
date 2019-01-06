<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Controller\Cart;

use Amasty\Cart\Model\Source\Option;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Checkout\Helper\Data as HelperData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\DataObjectFactory as ObjectFactory;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Amasty\Cart\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\LayoutInterface $layout,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        HelperData $helperData,
        Escaper $escaper,
        UrlHelper $urlHelper,
        ObjectFactory $objectFactory
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );

        $this->helper = $helper;
        $this->_productHelper = $productHelper;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->_view = $context->getView();
        $this->_coreRegistry = $coreRegistry;
        $this->urlHelper = $urlHelper;
        $this->catalogSession = $catalogSession;
        $this->categoryFactory = $categoryFactory;
        $this->layout = $layout;
        $this->escaper = $escaper;
        $this->cartHelper = $cartHelper;
        $this->localeResolver = $localeResolver;
        $this->objectFactory = $objectFactory;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $message = __('We can\'t add this item to your shopping cart right now. Please reload the page.');
            return $this->addToCartResponse($message, 0);
        }

        $params = $this->getRequest()->getParams();
        $product = $this->_initProduct();

        /**
         * Check product availability
         */
        if (!$product) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            return $this->addToCartResponse($message, 0);
        }
        $this->setProduct($product);

        try {
            if ($this->isShowOptionResponse($product, $params)) {
                $configurableParams = isset($params['super_attribute']) ? $params['super_attribute'] : null;
                return $this->showOptionsResponse($product, $configurableParams);
            }

            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->localeResolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $related = $this->getRequest()->getParam('related_product');
            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            if ($product->getTypeId() == "configurable"
                && $this->helper->getModuleConfig('display/disp_configurable_image')
            ) {
                $simpleProduct = $product->getTypeInstance()
                    ->getProductByAttributes($params['super_attribute'], $product);
                $this->_coreRegistry->register('amasty_cart_simple_product', $simpleProduct);
            } else {
                $this->_coreRegistry->unregister('amasty_cart_simple_product');
            }

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = '<p>' . __(
                            'You added %1 to your shopping cart.',
                            '<a href="' . $product->getProductUrl() .'" title=" . ' .
                            $product->getName() . '">' .
                            $product->getName() .
                            '</a>'
                        ) . '</p>';

                    $message = $this->getProductAddedMessage($product, $message);
                    return $this->addToCartResponse($message, 1);
                } else {
                    $message = '';
                    $errors = $this->cart->getQuote()->getErrors();
                    foreach ($errors as $error) {
                        $message .= $error->getText();
                    }
                    return $this->addToCartResponse($message, 0);
                }
            }
        } catch (LocalizedException $e) {
            return $this->addToCartResponse(
                $this->escaper->escapeHtml($e->getMessage()),
                0
            );

        } catch (\Exception $e) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            $message .= $e->getMessage();
            return $this->addToCartResponse($message, 0);
        }
    }

    private function isShowOptionResponse($product, $params)
    {
        $showOptionsResponse = false;
        switch ($product->getTypeId()) {
            case 'configurable':
                $attributesCount = $product->getTypeInstance()->getConfigurableAttributes($product)->count();
                $superParamsCount = (array_key_exists('super_attribute', $params)) ?
                    count($params['super_attribute']) : 0;
                if ($attributesCount != $superParamsCount) {
                    $showOptionsResponse = true;
                }
                break;
            case 'grouped':
                if (!array_key_exists('super_group', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'bundle':
                if (!array_key_exists('bundle_option', $params)) {
                    $showOptionsResponse = true;
                }
                break;
        }

        /* custom options block*/
        $needShowOptions = ($product->getTypeInstance()->hasRequiredOptions($product)
                && $product->getTypeId() != 'bundle')
            || ($this->helper->getModuleConfig('general/display_options') == Option::ALL_OPTIONS);
        if ($product->getOptions() && $needShowOptions && !array_key_exists('options', $params)) {
            $showOptionsResponse = true;
        }

        $result = $this->objectFactory->create(['data' => ['show_options_response' => $showOptionsResponse]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_is_show_option_response_after',
            ['controller' => $this, 'result' => $result]
        );

        return $result->getShowOptionsResponse();
    }

    /**
     * Creating options popup
     * @param Product $product
     * @param array|null $selectedOptions Selected configurable options
     * @return mixed
     */
    protected function showOptionsResponse(Product $product, $selectedOptions = null)
    {
        $this->_productHelper->initProduct($product->getEntityId(), $this);
        $page = $this->resultPageFactory->create(false, ['isIsolated' => true]);
        $page->addHandle('catalog_product_view');

        $type = $product->getTypeId();
        $page->addHandle('catalog_product_view_type_' . $type);

        $block = $page->getLayout()->getBlock('product.info');
        if (!$block) {
            $block = $page->getLayout()->createBlock(
                'Magento\Catalog\Block\Product\View',
                'product.info',
                [ 'data' => [] ]
            );
        }

        $block->setProduct($product);
        $html = $block->toHtml();

        $html = str_replace(
            '"spConfig',
            '"priceHolderSelector": ".price-box[data-product-id=' . $product->getId() . ']", "spConfig',
            $html
        );
        $title = '<a href="' . $product->getProductUrl() . '" title="' . $product->getName() . '" class="added-item">' .
            $product->getName() .
            '</a>';
        $contentClass = 'product-info-main';
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $contentClass .= ' product-item';
        }
        $html = '<div class="' . $contentClass . '" >' .
            $title .
            $html .
            '</div>';

        /* replace uenc for correct redirect*/
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $product->getProductUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        $html = str_replace($currentUenc, $newUenc, $html);

        $html = str_replace('"swatch-opt"', '"swatch-opt-' . $product->getId() . '"', $html);
        $html = str_replace('spConfig": {"attributes', 'spConfig": {"containerId":"#confirmBox", "attributes', $html);

        $result = [
            'title'     =>  __('Set options'),
            'message'   =>  $html,
            'b2_name'   =>  __('Add to cart'),
            'b1_name'   =>  __('Cancel'),
            'b2_action' =>  'self.submitFormInPopup();',
            'b1_action' =>  'self.confirmHide();',
            'align' =>  'self.confirmHide();' ,
            'is_add_to_cart' =>  '0'
        ];
        if ($selectedOptions) {
            $result['selected_options'] = $selectedOptions;
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_show_option_response_after',
            ['controller' => $this, 'product' => $product, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * @param Product $product
     * @param $message
     * @return string
     */
    protected function getProductAddedMessage(Product $product, $message)
    {
        if ($this->helper->displayProduct()) {
            $block = $this->layout->getBlock('amasty.cart.product');
            if (!$block) {
                $block = $this->layout->createBlock(
                    'Amasty\Cart\Block\Product',
                    'amasty.cart.product',
                    [ 'data' => [] ]
                );
            }

            $block->setProduct($product);
            $message = $block->toHtml();
        }

        //display count cart item
        if ($this->helper->displayCount()) {
            $summary = $this->cart->getSummaryQty();
            $cartUrl = $this->cartHelper->getCartUrl();
            if ($summary == 1) {
                $partOne = __('There is');
                $partTwo = __(' item');
            } else {
                $partOne = __('There are');
                $partTwo = __(' items');
            }

            $message .=
                "<p id='amcart-count' class='text'>".
                $partOne .
                ' <a href="'. $cartUrl .'" id="am-a-count" title="' . __('View Cart') . '">'.
                $summary.  $partTwo .
                '</a> '.
                __(' in your cart.') .
                "</p>";
        }

        //display sum price
        if ($this->helper->displaySumm()) {
            $message .=
                '<p class="text">' .
                __('Cart Subtotal:') .
                ' <span class="am_price">'.
                $this->getSubtotalHtml() .
                '</span></p>';
        }
        $message .= '<div class="border"></div>';

        //display related products
        $type = $this->helper->getModuleConfig('selling/block_type');
        if ($type && $type !== '0') {
            $this->_productHelper->initProduct($product->getEntityId(), $this);
            $this->layout->createBlock(
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                [ 'data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'use_link_for_as_low_as' => true
                ] ]
            );
            $relBlock = $this->layout->createBlock(
                'Amasty\Cart\Block\Product\\' . ucfirst($type),
                'amasty.cart.product_' . $type,
                [ 'data' => [] ]
            );
            $relBlock->setProduct($product)->setTemplate("Amasty_Cart::product/list/items.phtml");
            $message .= $relBlock->toHtml();

            /* replace uenc for correct redirect*/
            $currentUenc = $this->urlHelper->getEncodedUrl();
            $refererUrl = $this->_request->getServer('HTTP_REFERER');
            $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
            $message = str_replace($currentUenc, $newUenc, $message);
        }

        return $message;
    }

    /**
     * Creating finale popup
     * @param $message
     * @param $status
     * @return mixed
     */
    protected function addToCartResponse($message, $status)
    {
        $cartUrl = $this->cartHelper->getCartUrl();
        $result = [
            'title'     =>  __('Information'),
            'message'   =>  $message,
            'b1_name'   =>  __('Continue'),
            'b2_name'   =>  __('View Cart'),
            'b2_action' =>  'document.location = "' . $cartUrl . '";',
            'b1_action' =>  'self.confirmHide();',
            'is_add_to_cart' => $status,
            'checkout'  => '',
            'timer'     => ''
        ];

        if ($this->helper->getModuleConfig('display/disp_checkout_button')) {
            $goto = __('Go to Checkout');
            $result['checkout'] =
                '<a class="checkout"
                    title="' . $goto . '"
                    data-role="proceed-to-checkout"
                    href="' . $this->helper->getUrl('checkout') . '"
                    >
                    ' . $goto . '
                </a>';
        }

        $isProductView = $this->getRequest()->getParam('product_page');
        if ($isProductView == 'true' && $this->helper->getProductButton()) {
            $categoryId = $this->catalogSession->getLastVisitedCategoryId();
            if (!$categoryId && $this->getProduct()) {
                $productCategories = $this->getProduct()->getCategoryIds();
                if (count($productCategories) > 0) {
                    $categoryId = $productCategories[0];
                    if ($categoryId == $this->_storeManager->getStore()->getRootCategoryId()) {
                        if (isset($productCategories[1])) {
                            $categoryId = $productCategories[1];
                        } else {
                            $categoryId = null;
                        }
                    }
                }
            }
            if ($categoryId) {
                $category = $this->categoryFactory->create()->load($categoryId);
                if ($category) {
                    $result['b1_action'] =  'document.location = "'.
                        $category->getUrl()
                        .'";';
                }
            }

        }

        //add timer
        $time = $this->helper->getTime();
        if (0 < $time) {
            $result['timer'] .= '<span class="timer">' . '(' . $time . ')' . '</span>';
        }

        if ($status) {
            $result['product_sku'] = $this->getProduct()->getSku();
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_addtocart_response_after',
            ['controller' => $this, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * @return string
     */
    protected function getSubtotalHtml()
    {
        $totals = $this->cart->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) && $totals['subtotal'] instanceof Total
            ? $totals['subtotal']->getValue()
            : 0;

        return $this->helperData->formatPrice($subtotal);
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
