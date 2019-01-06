<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api\Data;

interface ShippingFeeInterface extends FeeInterface
{
    const DATA_TABLE_NAME = 'mageworx_multifees_fee_shipping_data';

    /**
     * Get assigned shipping methods in case it is a shipping fee
     *
     * @return array
     */
    public function getShippingMethods();

    /**
     * Assign shipping methods
     *
     * @param array $methods
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeInterface
     */
    public function setShippingMethods($methods = []);
}
