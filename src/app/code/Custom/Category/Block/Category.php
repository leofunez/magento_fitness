<?php

namespace Custom\Category\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Theme\Block\Html\Pager;

use Custom\ProductCard\Block\ProductCard;

use Magento\Framework\Exception\NoSuchEntityException;

class Category extends Template
{
    protected CategoryRepositoryInterface $categoryRepository;
    protected ProductCollection $productCollectionFactory;
    protected Toolbar $toolbarPrototype;
    protected Visibility $productVisibility;
    protected StoreManagerInterface $storeManager;
    protected $request;

    /** @var Collection|null */
    private $collection = null;

    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollection $productCollection,
        Toolbar $toolbar,
        Visibility $productVisibility,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollection;
        $this->toolbarPrototype = $toolbar; // we will create fresh blocks via layout anyway
        $this->productVisibility = $productVisibility;
        $this->storeManager = $storeManager;
        $this->request = $context->getRequest();

        parent::__construct($context, $data);
    }

    /**
     * Build or return the memoized product collection
     */
    public function getProducts()
    {
        if ($this->collection !== null) {
            return $this->collection;
        }

        $categoryId = (int) $this->request->getParam('id');
        $storeId    = (int) $this->storeManager->getStore()->getId();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'thumbnail', 'sku']);
        $collection->setStore($storeId);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->group('e.entity_id');

        // Join price data so sorting by 'price' is reliable
        $collection->addFinalPrice();

        // Category filter
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId, $storeId);
                $collection->addCategoryFilter($category);
                $collection->addUrlRewrite($categoryId);
            } catch (NoSuchEntityException $e) {
                // Return empty if category missing
                $collection->addFieldToFilter('entity_id', ['in' => []]);
            }
        }

        // Pagination (limit + page) from request
        $currentPage = (int) $this->request->getParam('p', 1);
        $pageSize    = (int) $this->request->getParam('product_list_limit', 12);
        $collection->setCurPage($currentPage);
        $collection->setPageSize($pageSize);

        // Apply sort from request (toolbar vars) OR default to 'position'
        $orderVar = 'product_list_order';
        $dirVar   = 'product_list_dir';

        $order = $this->request->getParam($orderVar);
        $dir   = strtolower((string) $this->request->getParam($dirVar)) ?: 'asc';

        if ($order) {
            // ensure order is one of allowed attributes
            if (in_array($order, ['name', 'price', 'position'], true)) {
                $collection->addAttributeToSort($order, ($dir === 'desc' ? 'DESC' : 'ASC'));
            }
        } else {
            // default only when no order chosen
            $collection->addAttributeToSort('position', 'ASC');
        }

        // memoize
        $this->collection = $collection;
        return $this->collection;
    }

    /**
     * Toolbar block configured to the same collection instance
     */
    public function getToolbar()
    {
        $collection = $this->getProducts();

        /** @var Toolbar $toolbar */
        $toolbar = $this->getLayout()->createBlock(Toolbar::class, 'custom.category.products.toolbar');
        $toolbar->setAvailableOrders([
            'name'     => __('Name'),
            'price'    => __('Price'),
            'position' => __('Position'),
        ]);

        $toolbar->setDefaultOrder('position');
        $toolbar->setDefaultDirection('asc');
        $toolbar->setCollection($collection);

        // Optionally align var names to default Magento ones
        $toolbar->setData('order_var_name', 'product_list_order');
        $toolbar->setData('direction_var_name', 'product_list_dir');
        $toolbar->setData('limit_var_name', 'product_list_limit');

        return $toolbar;
    }

    /**
     * Pager block bound to the SAME collection
     */
    public function getPager()
    {
        $collection = $this->getProducts();

        /** @var Pager $pager */
        $pager = $this->getLayout()->createBlock(Pager::class, 'custom.category.products.pager');

        if ($pager) {
            $pager->setAvailableLimit([12 => 12, 24 => 24, 48 => 48]);
            $pager->setShowAmounts(false);
            $pager->setShowPerPage(false);
            $pager->setCollection($collection);

            return $pager->toHtml();
        }

        return '';
    }

    public function getProductCard(int $productId): ?string
    {
        if (!$productId) return null;

        $productCard = $this->getLayout()
            ->createBlock(ProductCard::class)
            ->setTemplate('Custom_ProductCard::product-card.phtml')
            ->setData('product_id', $productId)
            ->toHtml();

        return $productCard ?: null;
    }
}
