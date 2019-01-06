<?php

namespace FME\Events\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use FME\Events\Block\Adminhtml\Event\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class Actions extends Column
{

    const EVENT_URL_PATH_EDIT = 'events/event/edit';
    const EVENT_URL_PATH_DELETE = 'events/event/delete';

    protected $actionUrlBuilder;
    protected $urlBuilder;
    private $editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::EVENT_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
        
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['event_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['event_id' => $item['event_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::EVENT_URL_PATH_DELETE,
                            ['event_id' => $item['event_id']]
                        ),
                       'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete \"${ $.$data.event_name }\"'),
                            'message' => __('Are you sure you wan\'t to delete a \"${ $.$data.event_name }\" record?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
