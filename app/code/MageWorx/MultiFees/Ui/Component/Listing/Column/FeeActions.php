<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Ui\Component\Listing\Column;

class FeeActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path  to edit
     *
     * @var string
     */
    const URL_PATH_EDIT = 'mageworx_multifees/*/edit';

    /**
     * Url path  to delete
     *
     * @var string
     */
    const URL_PATH_DELETE = 'mageworx_multifees/*/delete';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {


        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \MageWorx\MultiFees\Exception\RefactoringException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['fee_id'])) {
                    $feeType = $item[\MageWorx\MultiFees\Api\Data\FeeInterface::TYPE];
                    switch ($feeType) {
                        case \MageWorx\MultiFees\Api\Data\FeeInterface::CART_TYPE:
                            $controller = '/fee_cart/';
                            break;
                        case \MageWorx\MultiFees\Api\Data\FeeInterface::SHIPPING_TYPE:
                            $controller = '/fee_shipping/';
                            break;
                        case \MageWorx\MultiFees\Api\Data\FeeInterface::PAYMENT_TYPE:
                            $controller = '/fee_payment/';
                            break;
                        default:
                            throw new \MageWorx\MultiFees\Exception\RefactoringException('Unknown fee type');
                    }
                    $urlPathEdit                  = str_ireplace('/*/', $controller, static::URL_PATH_EDIT);
                    $urlPathDelete                = str_ireplace('/*/', $controller, static::URL_PATH_DELETE);
                    $item[$this->getData('name')] = [
                        'edit'   => [
                            'href'  => $this->urlBuilder->getUrl(
                                $urlPathEdit,
                                [
                                    'fee_id' => $item['fee_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href'    => $this->urlBuilder->getUrl(
                                $urlPathDelete,
                                [
                                    'fee_id' => $item['fee_id']
                                ]
                            ),
                            'label'   => __('Delete'),
                            'confirm' => [
                                'title'   => __('Delete "${ $.$data.name }"'),
                                'message' => __('Are you sure you wan\'t to delete the Fee "${ $.$data.name }" ?')
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
