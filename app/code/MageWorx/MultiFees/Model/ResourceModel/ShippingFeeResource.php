<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\ResourceModel;

use Magento\Framework\EntityManager\EntityMetadataInterface;

class ShippingFeeResource extends FeeAbstractResource
{
    const FEE_ENTITY_TYPE = 'MageWorx\MultiFees\Api\Data\ShippingFeeInterface';

    /**
     * Get meta data of corresponding entity
     *
     * @return EntityMetadataInterface
     * @throws \Exception
     */
    public function getCorrespondingMetaData()
    {
        return $this->metadataPool->getMetadata(static::FEE_ENTITY_TYPE);
    }
}
