<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Config Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * Config paths to settings
     */
    const FEE_CART_ENABLE                  = 'mageworx_multifees/main/enable_cart';
    const FEE_APPLY_ON_CLICK               = 'mageworx_multifees/main/apply_fee_on_click';
    const FEE_INCLUDE_IN_SHIPPING_PRICE    = 'mageworx_multifees/main/include_fee_in_shipping_price';
    const FEE_TAX_CALCULATION_INCLUDES_TAX = 'mageworx_multifees/main/tax_calculation_includes_tax';
    const FEE_DISPLAY_TAX_IN_BLOCK         = 'mageworx_multifees/main/display_tax_in_block';
    const FEE_DISPLAY_TAX_IN_CART          = 'mageworx_multifees/main/display_tax_in_cart';
    const FEE_DISPLAY_TAX_IN_SALES         = 'mageworx_multifees/main/display_tax_in_sales';

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_CART_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is a fee should be applied when the corresponding fee clicked (Yes)
     * or by pressing the button "Apply Fee" (No)
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isApplyOnClick($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_APPLY_ON_CLICK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isIncludeFeeInShippingPrice($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_INCLUDE_IN_SHIPPING_PRICE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isTaxCalculationIncludesTax($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::FEE_TAX_CALCULATION_INCLUDES_TAX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getTaxInCart($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_DISPLAY_TAX_IN_CART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getTaxInSales($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_DISPLAY_TAX_IN_SALES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getTaxInBlock($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::FEE_DISPLAY_TAX_IN_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
