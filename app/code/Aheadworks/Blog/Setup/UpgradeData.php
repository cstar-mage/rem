<?php
/**
 * Copyright 2018 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Aheadworks\Blog\Model\Source\Post\CustomerGroups;

/**
 * Class UpgradeData
 * @package Aheadworks\Blog\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion() && version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->updateCustomerGroupsForPosts($setup);
        }
        $setup->endSetup();
    }

    /**
     * Fill up all 'customer_groups' fields with 'all groups' value
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function updateCustomerGroupsForPosts(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->update(
            $setup->getTable('aw_blog_post'),
            [
                'customer_groups' => CustomerGroups::ALL_GROUPS
            ]
        );
    }
}
