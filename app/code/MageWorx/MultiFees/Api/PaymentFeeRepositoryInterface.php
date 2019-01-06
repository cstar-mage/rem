<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Api;

/**
 * Fee CRUD interface.
 *
 * @api
 */
interface PaymentFeeRepositoryInterface
{
    /**
     * Save fee
     *
     * @param \MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee
     * @return \MageWorx\MultiFees\Api\Data\PaymentFeeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee);

    /**
     * Retrieve fee
     *
     * @param int $feeId
     * @return \MageWorx\MultiFees\Api\Data\PaymentFeeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($feeId);

    /**
     * Delete fee
     *
     * @param \MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee);

    /**
     * Delete fee by ID.
     *
     * @param int $feeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($feeId);
}
