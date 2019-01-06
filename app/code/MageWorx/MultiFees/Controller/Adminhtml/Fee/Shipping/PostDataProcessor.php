<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee\Shipping;

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

        if (isset($data[FeeInterface::SHIPPING_METHODS])) {
            $data[FeeInterface::SHIPPING_METHODS] = implode(',', $data[FeeInterface::SHIPPING_METHODS]);
        } else {
            $data[FeeInterface::SHIPPING_METHODS] = '';
        }

        return (new \Zend_Filter_Input([], [], $data))->getUnescaped();
    }
}
