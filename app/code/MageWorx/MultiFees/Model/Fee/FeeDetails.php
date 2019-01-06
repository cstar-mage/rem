<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee;

use MageWorx\MultiFees\Api\Data\FeeDetailsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Fee Details Model
 */
class FeeDetails extends AbstractSimpleObject implements FeeDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const PRICE   = 'price';
    const OPTIONS = 'options';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return isset($this->_data['price']) ? $this->_data['price'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData('price', $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return isset($this->_data['options']) ? $this->_data['options'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        return $this->setData('options', $options);
    }
}
