<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Plugin\Quote;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;

class FeeDetailsPlugin
{
    /**
     * @var \MageWorx\MultiFees\Api\Data\FeeDetailsInterfaceFactory
     */
    protected $detailsFactory;

    /**
     * @var \MageWorx\MultiFees\Api\Data\FeeOptionsInterfaceFactory
     */
    protected $optionsFactory;

    /**
     * @var TotalSegmentExtensionFactory
     */
    protected $totalSegmentExtensionFactory;

    /**
     * @var \MageWorx\MultiFees\Helper\Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $code;

    /**
     * FeeDetailsPlugin constructor.
     *
     * @param \MageWorx\MultiFees\Api\Data\FeeDetailsInterfaceFactory $detailsFactory
     * @param \MageWorx\MultiFees\Api\Data\FeeOptionsInterfaceFactory $optionsFactory
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param \MageWorx\MultiFees\Helper\Data $helperData
     */
    public function __construct(
        \MageWorx\MultiFees\Api\Data\FeeDetailsInterfaceFactory $detailsFactory,
        \MageWorx\MultiFees\Api\Data\FeeOptionsInterfaceFactory $optionsFactory,
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        \MageWorx\MultiFees\Helper\Data $helperData
    ) {
        $this->detailsFactory               = $detailsFactory;
        $this->optionsFactory               = $optionsFactory;
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->helperData                   = $helperData;
        $this->code                         = 'mageworx_fee';
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getOptionsData($options)
    {
        $feeOptions = [];
        foreach ($options as $option) {
            /** @var \MageWorx\MultiFees\Api\Data\FeeOptionsInterface $feeOption */
            $feeOption = $this->optionsFactory->create([]);
            $percent   = !empty($option['percent']) ? $option['percent'] : '';
            $feeOption->setPercent($percent);
            $feeOption->setTitle($option['title']);
            $feeOption->setPrice($option['price']);
            $feeOptions[] = $feeOption;
        }

        return $feeOptions;
    }

    /**
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
        \Closure $proceed,
        array $addressTotals = []
    ) {
        $totalSegments = $proceed($addressTotals);

        if (!array_key_exists($this->code, $addressTotals)) {
            return $totalSegments;
        }

        $fees = $addressTotals['mageworx_fee']->getData();
        if (!array_key_exists('full_info', $fees)) {
            return $totalSegments;
        }

        $detailsId = 1;
        $finalData = [];
        $fullInfo  = $fees['full_info'];
        if (is_string($fullInfo)) {
            $fullInfo = unserialize($fullInfo);
        }

        if (!$fullInfo) {
            return $totalSegments;
        }

        foreach ($fullInfo as $info) {
            $feeDetails = $this->detailsFactory->create([]);
            $feeOptions = $this->getOptionsData($info['options']);
            $feeDetails->setOptions($feeOptions);
            $finalData[] = $feeDetails;
            $detailsId++;
        }
        $attributes = $totalSegments[$this->code]->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }
        $attributes->setMageworxFeeDetails($finalData);
        $totalSegments[$this->code]->setExtensionAttributes($attributes);

        return $totalSegments;
    }
}
