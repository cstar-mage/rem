<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class ShippingAddressManager extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var array
     */
    private $allowedShippingAddressDataFields;

    /**
     * ShippingAddressManager constructor.
     *
     * @param Context $context
     * @param \Magento\Checkout\Model\Cart $cart
     * @param array $allowedShippingAddressDataFields
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Cart $cart,
        $allowedShippingAddressDataFields = []
    ) {
        parent::__construct($context);
        $this->cart                             = $cart;
        $this->allowedShippingAddressDataFields = $allowedShippingAddressDataFields;
    }

    /**
     * Converts input string from a camelCase to a snake_case
     *
     * @author cletus
     * @source https://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
     *
     * @param string $input
     * @return string
     */
    public function convertFromCamelCaseToSnakeCase($input)
    {
        if (!is_string($input)) {
            return $input;
        }

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Saving and transferring shipping address data to the quotes shipping address
     *
     * @param array $shippingAddressData
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function transferShippingAddressDataToTheAddressObject($shippingAddressData = [])
    {
        if (!$shippingAddressData && !is_array($shippingAddressData)) {
            $shippingAddressData = [];
        }

        $snakeCasedShippingAddressData = [];
        foreach ($shippingAddressData as $dataKey => $dataValue) {
            $cameCaseKey                                 = $this->convertFromCamelCaseToSnakeCase($dataKey);
            $snakeCasedShippingAddressData[$cameCaseKey] = $dataValue;
        }

        $allowedShippingAddressData = $this->filterShippingAddressData($snakeCasedShippingAddressData);
        /** @var \Magento\Quote\Model\Quote $cartQuote */
        $cartQuote = $this->cart->getQuote();
        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $cartQuote->getShippingAddress();
        $shippingAddress->addData($allowedShippingAddressData);

        return $shippingAddress;
    }

    /**
     * Remove from the data array all not allowed fields
     *
     * @param array $data
     * @return array
     */
    private function filterShippingAddressData($data = [])
    {
        $data = array_filter(
            $data,
            function ($key) {
                return in_array(
                    $key,
                    $this->allowedShippingAddressDataFields
                );
            },
            ARRAY_FILTER_USE_KEY
        );

        return $data;
    }
}
