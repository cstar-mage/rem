<?php
/**
 * Copyright 2018 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller;

use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\TagRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Blog\Model\Url\TypeResolver as UrlTypeResolver;
use Aheadworks\Blog\Controller\Checker as DataChecker;

/**
 * Class Action
 * @package Aheadworks\Blog\Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var UrlTypeResolver
     */
    protected $urlTypeResolver;

    /**
     * @var DataChecker
     */
    protected $dataChecker;

    /**
     * Action constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param StoreManagerInterface $storeManager
     * @param Registry $coreRegistry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PostRepositoryInterface $postRepository
     * @param TagRepositoryInterface $tagRepository
     * @param Config $config
     * @param Url $url
     * @param UrlTypeResolver $urlTypeResolver
     * @param DataChecker $dataChecker
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        TagRepositoryInterface $tagRepository,
        Config $config,
        Url $url,
        UrlTypeResolver $urlTypeResolver,
        DataChecker $dataChecker
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->tagRepository = $tagRepository;
        $this->config = $config;
        $this->url = $url;
        $this->urlTypeResolver = $urlTypeResolver;
        $this->dataChecker = $dataChecker;
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->config->isBlogEnabled()) {
            /**  @var \Magento\Framework\Controller\Result\Forward $forward */
            $forward = $this->resultForwardFactory->create();
            return $forward->forward('noroute');
        }
        $this->coreRegistry->register('blog_action', true, true);
        return parent::dispatch($request);
    }

    /**
     * Retrieves blog title
     *
     * @return string
     */
    protected function getBlogTitle()
    {
        return $this->config->getBlogTitle();
    }

    /**
     * Retrieves blog meta description
     *
     * @return mixed
     */
    protected function getBlogMetaDescription()
    {
        return $this->config->getBlogMetaDescription();
    }

    /**
     * Get current store ID
     *
     * @return int
     */
    protected function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Go back
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
