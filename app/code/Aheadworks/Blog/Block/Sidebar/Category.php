<?php
/**
 * Copyright 2018 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Sidebar;

use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Model\Source\Category\Status as CategoryStatus;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Category sidebar
 * @package Aheadworks\Blog\Block\Sidebar
 */
class Category extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     *  Additional css classes
     */
    const HIDE_CSS_CLASS = 'hide';
    const SHADED_CSS_CLASS = 'shaded';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var string
     */
    private $categoryDisplayLimit;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        Url $url,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $context->getStoreManager();
        $this->config = $config;
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve categories
     *
     * @return CategoryInterface[]
     */
    public function getCategories()
    {
        $this->searchCriteriaBuilder
            ->addFilter(CategoryInterface::STATUS, CategoryStatus::ENABLED)
            ->addFilter(CategoryInterface::STORE_IDS, $this->storeManager->getStore()->getId())
            ->addSortOrder(
                new SortOrder(
                    [
                        SortOrder::FIELD => CategoryInterface::SORT_ORDER,
                        SortOrder::DIRECTION => SortOrder::SORT_ASC
                    ]
                )
            );
        return $this->categoryRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * Get category limit for displaying
     *
     * @return int|null
     */
    public function getNumCategoriesToDisplay()
    {
        if ($this->categoryDisplayLimit === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->categoryDisplayLimit = $this->config->getNumCategoriesToDisplay($storeId);
        }
        return $this->categoryDisplayLimit;
    }

    /**
     * Retrieve category url
     *
     * @param CategoryInterface $category
     * @return string
     */
    public function getCategoryUrl(CategoryInterface $category)
    {
        return $this->url->getCategoryUrl($category);
    }

    /**
     * Get additional css class for category item
     *
     * @param int $categoryItemIndex
     * @return string
     */
    public function getAdditionalClass(int $categoryItemIndex)
    {
        $categoryLimit = $this->getNumCategoriesToDisplay();
        if ($categoryLimit > 0 && $categoryItemIndex > $categoryLimit) {
            return self::HIDE_CSS_CLASS;
        }
        if ($categoryLimit > 0 && $categoryItemIndex == $categoryLimit) {
            return self::SHADED_CSS_CLASS;
        }
        return '';
    }

    /**
     * Check if need to display show more links
     *
     * @param int $categoryItemIndex
     * @return bool
     */
    public function isNeedToDisplayShowMoreLinks(int $categoryItemIndex)
    {
        $categoryLimit = $this->getNumCategoriesToDisplay();
        if ($categoryLimit > 0 && $categoryItemIndex > $categoryLimit) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [\Aheadworks\Blog\Model\Category::CACHE_TAG_CATEGORY_SIDEBAR];
    }
}
