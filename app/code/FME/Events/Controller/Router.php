<?php
namespace FME\Events\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{

    protected $actionFactory;
    protected $_response;
    protected $_request;
    protected $pageRepository;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \FME\Events\Helper\Data $helper,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_request = $request;
        $this->pageRepository = $pageRepository;
        $this->_response = $response;
        $this->eventsHelper = $helper;
    }
    
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
            $route = $this->eventsHelper->getEventSeoIdentifier();
            $suffix = $this->eventsHelper->getEventSeoSuffix();
            $identifier = trim($request->getPathInfo(), '/');
            $parts = explode('/', $identifier);
            
            $identifie = $route.$suffix;
            $identifieDetail = 'detail';
            
        if (strcmp($identifier, $identifie) == 0) {
            $request->setModuleName('event')->setControllerName('Index')->setActionName('Index');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        } elseif (isset($parts[0]) && ($parts[0] == $route) && isset($parts[1]) && $parts[1] == 'calendar'.$suffix) {
            $request->setModuleName('event')->setControllerName('Index')->setActionName('Calendar');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        } elseif (isset($parts[0]) && ($parts[0] == $route) && isset($parts[1])) {
              $detailIdentifier =  $parts[1];
            if (strpos($detailIdentifier, '.') !== false) {
                $detailIdentifier = explode('.', $detailIdentifier);
                $detailIdentifier = $detailIdentifier[0];
            }
             
              $request->setModuleName('event')->setControllerName('Index')->setActionName('Detail')->setParam('id', $detailIdentifier);
              $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        } else {
              return null;
        }
                
            return $this->actionFactory->create(
                'Magento\Framework\App\Action\Forward',
                ['request' => $request]
            );
    }
}
