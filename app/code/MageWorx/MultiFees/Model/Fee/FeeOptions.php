<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Fee;

use MageWorx\MultiFees\Api\Data\FeeOptionsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Grand Total Tax Details Model
 */
class FeeOptions extends AbstractSimpleObject implements FeeOptionsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const PERCENT = 'percent';
    const TITLE   = 'title';
    const PRICE   = 'price';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getPercent()
    {
        return $this->_get(self::PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPercent($percent)
    {
        return $this->setData(self::PERCENT, $percent);
    }
}
