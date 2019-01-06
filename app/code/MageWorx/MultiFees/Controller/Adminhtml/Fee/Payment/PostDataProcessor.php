<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Payment;

use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Controller\Adminhtml\Fee\AbstractPostDataProcessor;

class PostDataProcessor extends AbstractPostDataProcessor
{
    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    public function filter($data)
    {
        /** @var array $data */
        $data = $this->filterCommonData($data);

        if (isset($data[FeeInterface::PAYMENT_METHODS])) {
            $data[FeeInterface::PAYMENT_METHODS] = implode(',', $data[FeeInterface::PAYMENT_METHODS]);
        } else {
            $data[FeeInterface::PAYMENT_METHODS] = '';
        }

        return (new \Zend_Filter_Input([], [], $data))->getUnescaped();
    }
}
