<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api\Data;

interface PaymentFeeInterface extends FeeInterface
{
    const DATA_TABLE_NAME = 'mageworx_multifees_fee_payment_data';

    /**
     * Get assigned shipping methods in case it is a shipping fee
     *
     * @return array
     */
    public function getShippingMethods();

    /**
     * Get assigned payment methods in case it is a payment fee
     *
     * @return array
     */
    public function getPaymentMethods();

    /**
     * Assign shipping methods
     *
     * @param array $methods
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setShippingMethods($methods = []);

    /**
     * Assign payment methods
     *
     * @param array $methods
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setPaymentMethods($methods = []);
}
