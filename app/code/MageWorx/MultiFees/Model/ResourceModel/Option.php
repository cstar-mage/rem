<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel;

class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('mageworx_multifees_fee_option', 'fee_option_id');
    }

    /**
     * @param $feeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllOptions($feeId)
    {
        $read   = $this->getConnection();
        $result = $read->fetchAssoc(
            $read->select()
                 ->from($this->getMainTable())
                 ->where('fee_id = ?', $feeId)
        );

        return $result;
    }
}
