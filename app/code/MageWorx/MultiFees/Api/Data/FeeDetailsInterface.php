<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api\Data;

/**
 * Interface FeeDetailsInterface
 *
 * @api
 */
interface FeeDetailsInterface
{
    /**
     * Get fee amount price value
     *
     * @return float|string
     */
    public function getPrice();

    /**
     * @param string|float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Applied fee options info
     *
     * @return \MageWorx\MultiFees\Api\Data\FeeOptionsInterface[]
     */
    public function getOptions();

    /**
     * @param \MageWorx\MultiFees\Api\Data\FeeOptionsInterface[] $options
     * @return $this
     */
    public function setOptions($options);
}
