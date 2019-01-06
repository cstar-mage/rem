<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use MageWorx\MultiFees\Api\Data\FeeInterface;
use MageWorx\MultiFees\Block\FeeFormInput\FeeFormInputRenderInterface;

/**
 * Class FeeFormInputFactory
 * Factory where you can create the form input for the corresponding fee by its type
 *
 * @package MageWorx\MultiFees\Block
 */
final class FeeFormInputPlant
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array|FeeFormInputRenderInterface[]
     */
    private $feeRendererByItsType;

    /**
     * Factory construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param FeeFormInputRenderInterface[] $feeRendererByItsType
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $feeRendererByItsType = []
    ) {
        $this->objectManager        = $objectManager;
        $this->feeRendererByItsType = $feeRendererByItsType;
    }

    /**
     * Create form input instance
     *
     * @param FeeInterface $fee
     * @param array $data
     * @return FeeFormInputRenderInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    final public function create(FeeInterface $fee, array $data = [])
    {
        if (empty($this->feeRendererByItsType[$fee->getInputType()])) {
            throw new NoSuchEntityException(__('There no render for the fee type %1', $fee->getInputType()));
        }

        $instanceName = $this->feeRendererByItsType[$fee->getInputType()];
        $data['fee']  = $fee;

        /** @var $render FeeFormInputRenderInterface */
        $render = $this->objectManager->create(
            $instanceName,
            $data
        );

        if (!$render instanceof FeeFormInputRenderInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    '%1 doesn\'t implement \MageWorx\MultiFees\Api\FeeFormInputRenderInterface',
                    [$instanceName]
                )
            );
        }

        return $render;
    }
}
