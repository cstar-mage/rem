<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block\Adminhtml\Stock\Renderer;

use \Magento\Framework\DataObject;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    private $websiteFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->websiteFactory = $websiteFactory;
    }

    public function render(DataObject $row)
    {
        $website = $row->getWebsiteId();
        $websites = $this->websiteFactory->create()->getCollection()->toOptionArray();
        $sites = explode(',', $website);
        $webSitesLabels = [];
        foreach ($websites as $v) {
            if (array_search($v['value'], $sites) !== false) {
                $webSitesLabels[] = $v['label'];
            }
        }
        $website = implode(", ", array_unique($webSitesLabels));
        return $website;
    }
}
