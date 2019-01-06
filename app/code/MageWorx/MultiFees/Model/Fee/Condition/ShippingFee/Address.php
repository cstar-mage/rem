<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Condition\ShippingFee;

use MageWorx\MultiFees\Exception\ValidationException;

/**
 * Class Address
 *
 * @method string getAttribute()
 * @method array getAttributeOption()
 * @method Address setInputType($string)
 * @method Address setOperator($string)
 * @method Address setValue($string)
 */
class Address extends \MageWorx\MultiFees\Model\Fee\Condition\Address
{
    /**
     * Additional attributes, specific for each entity, which should be set in that condition only
     *
     * @var array
     */
    protected $attributesAdditional = [
        'postcode'   => 'Shipping Postcode',
        'region'     => 'Shipping Region',
        'region_id'  => 'Shipping State/Province',
        'country_id' => 'Shipping Country',
    ];

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return \Magento\Framework\Model\AbstractModel
     * @throws ValidationException
     */
    protected function resolveAddressEntity(\Magento\Framework\Model\AbstractModel $model)
    {
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address ||
            !$address->getAddressType() == \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING
        ) {
            $this->validateModelEntity($model);
            $address = $model->getQuote()->getShippingAddress();
        }

        if (!$address) {
            throw new ValidationException(__('Empty shipping address'));
        }

        return $address;
    }
}
