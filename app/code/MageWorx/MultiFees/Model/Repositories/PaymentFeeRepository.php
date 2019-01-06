<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Repositories;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\MultiFees\Api\PaymentFeeRepositoryInterface;
use MageWorx\MultiFees\Model\ResourceModel\PaymentFeeResource as ResourceFee;

/**
 * Class PaymentFeeRepository
 */
class PaymentFeeRepository implements PaymentFeeRepositoryInterface
{
    /**
     * @var ResourceFee
     */
    protected $resource;

    /**
     * @var \MageWorx\MultiFees\Model\FeeFactory
     */
    protected $feeFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * FeeRepository constructor.
     *
     * @param ResourceFee $resource
     * @param \MageWorx\MultiFees\Model\PaymentFeeFactory $feeFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceFee $resource,
        \MageWorx\MultiFees\Model\PaymentFeeFactory $feeFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resource     = $resource;
        $this->feeFactory   = $feeFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Fee data
     *
     * @param \MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee
     * @return \MageWorx\MultiFees\Api\Data\PaymentFeeInterface
     * @throws CouldNotSaveException
     */
    public function save(\MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee)
    {
        try {
            $this->resource->save($fee);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the fee: %1',
                    $exception->getMessage()
                )
            );
        }

        return $fee;
    }

    /**
     * Load Fee data by ID
     *
     * @param int $feeId
     * @return \MageWorx\MultiFees\Api\Data\PaymentFeeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($feeId)
    {
        /** @var \MageWorx\MultiFees\Model\PaymentFee|\MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee */
        $fee = $this->feeFactory->create();
        $this->resource->load($fee, $feeId);
        if (!$fee->getId()) {
            throw new NoSuchEntityException(__('Fee with id "%1" does not exist.', $feeId));
        }

        return $fee;
    }

    /**
     * Delete Fee
     *
     * @param \MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\MageWorx\MultiFees\Api\Data\PaymentFeeInterface $fee)
    {
        try {
            $this->resource->delete($fee);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the fee: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * Delete Fee by given Fee ID
     *
     * @param int $feeId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($feeId)
    {
        return $this->delete($this->getById($feeId));
    }
}
