<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Controller\Adminhtml\Fee;

use Magento\Framework\Exception\LocalizedException;

abstract class AbstractPostDataProcessor
{
    const ADMIN_STORE_ID = 0;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->dateFilter     = $dateFilter;
        $this->messageManager = $messageManager;
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    abstract public function filter($data);

    /**
     * Pre-filter common data for all types of a fee
     *
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    protected function filterCommonData(array $data)
    {
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }

        if (empty($data['fee_id'])) {
            $data['fee_id'] = null;
        }

        if (array_search(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $data['stores']) !== false) {
            $data['stores'] = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        }

        if (isset($data['applied_totals'])) {
            $data['applied_totals'] = implode(',', $data['applied_totals']);
        }

        if (!empty($data['option']['position']) && !empty($data['option']['is_default'])) {
            // Clear multiple "is_default" values for drop-down, radio
            foreach ($data['option']['position'] as $optionId => $position) {
                if (isset($data['option']['is_default'][$optionId])) {
                    $data['option']['is_default'][$optionId] = '1';
                } else {
                    $data['option']['is_default'][$optionId] = '0';
                }
            }
        }

        if (!empty($data['option']['position']) && empty($data['option']['is_default'])) {
            // Add "is_default" to first option for required fees (drop-down, radio)
            foreach ($data['option']['position'] as $optionId => $position) {
                if (isset($data['option']['is_default'][$optionId])) {
                    $data['option']['is_default'][$optionId] = '1';
                } else {
                    $data['option']['is_default'][$optionId] = '0';
                }
            }
        }

        if (!empty($data['option']['value'])) {
            foreach ($data['option']['value'] as $valueId => $labels) {
                if (empty($data['option']['value'][$valueId][static::ADMIN_STORE_ID])) {
                    throw new LocalizedException(__('Option title is required for the default (admin) scope.'));
                }
            }
        }

        unset($data['rule']['conditions']);

        return $data;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool Return FALSE if someone item is invalid
     */
    public function validate($data)
    {
        $isEmptyOptions = false;

        if (!isset($data['option']['value'])) {
            $isEmptyOptions = true;
        }

        if (isset($data['option']['delete'])
            && count(array_filter($data['option']['delete'])) >= count($data['option']['value'])
        ) {
            $isEmptyOptions = true;
        }

        if (isset($data['option']['price'])) {
            foreach ($data['option']['price'] as $price) {
                if ($price < 0) {
                    $this->messageManager->addErrorMessage(__('Price could not be a negative number.'));

                    return false;
                }
            }
        }

        if ($isEmptyOptions) {
            $this->messageManager->addErrorMessage(
                __('The "Fee Options" field is empty. Add some options for the fee to proceed.')
            );

            return false;
        }

        return true;
    }

    /**
     * Check if required fields empty
     *
     * @param array $data
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'title'  => __('Name'),
            'stores' => __('Store View'),
            'status' => __('Status')
        ];

        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill in required "%1" field', $requiredFields[$field])
                );
            }
        }

        return $errorNo;
    }
}
