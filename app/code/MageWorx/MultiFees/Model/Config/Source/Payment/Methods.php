<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Config\Source\Payment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Store\Model\ScopeInterface;

class Methods implements ArrayInterface
{
    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param PaymentConfig $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {

        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig   = $scopeConfig;
    }

    /**
     * Return array of all payment methods.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods     = [];
        $dataStorage = [];
        foreach ($this->scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, null) as $code => $data) {
            $dataStorage[$code] = $data;
            $methods[$code]     = [
                'value' => $code,
                'label' => !empty($data['title']) ? __($data['title']) : __('Code: ') . $code
            ];
        }

        return $methods;
    }
}
