<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee\Condition\CartFee;

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
    protected $attributesAdditional = [];

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return \Magento\Framework\Model\AbstractModel
     * @throws ValidationException
     */
    protected function resolveAddressEntity(\Magento\Framework\Model\AbstractModel $model)
    {
        return parent::resolveAddressEntity($model);
    }
}
