<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Exception;

use \Exception;

class ValidationException extends Exception
{
    protected $isValidResult = false;

    /**
     * Set is result should be valid or not
     *
     * @param $bool
     * @return $this
     */
    public function setIsValidResult($bool)
    {
        $this->isValidResult = (bool)$bool;

        return $this;
    }

    /**
     * Get is result should be valid or not
     *
     * @return bool
     */
    public function getIsValidResult()
    {
        return (bool)$this->isValidResult;
    }
}
