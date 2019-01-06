<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api\Data;

/**
 * Interface FeeOptionsInterface
 *
 * @api
 */
interface FeeOptionsInterface
{
    /**
     * Get fee percentage value
     *
     * @return string
     */
    public function getPercent();

    /**
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);

    /**
     * Fee option title
     *
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Fee option price
     *
     * @return float|string
     */
    public function getPrice();

    /**
     * @param float|string $price
     * @return $this
     */
    public function setPrice($price);
}
