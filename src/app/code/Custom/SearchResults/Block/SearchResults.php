<?php

namespace Custom\SearchResults\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Search\Model\QueryFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Theme\Block\Html\Pager;

use Custom\ProductCard\Block\ProductCard;

class SearchResults extends Template {
    private $queryFactory;
    private ProductCollection $productCollectionFactory;

    /** @var Collection|null */
    private $collection = null;
    
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        ProductCollection $productCollection,
        array $data = []
    ) {
        $this->queryFactory = $queryFactory;
        $this->productCollectionFactory = $productCollection;
        parent::__construct($context, $data);
    }

    /**
     * Get the current search query text
     * 
     * @return string
     */
    public function getSearchQuery(): string {
        $query = (string) $this->queryFactory->get()->getQueryText();

        return $query;
    }


    /**
     * Get all searched products based on query text
     * @return ProductCollection
     */
    public function getProducts() {
        if ($this->collection !== null) {
            return $this->collection;
        }

        $queryText = $this->getSearchQuery();

        if ($queryText === '') {
            return $this->productCollectionFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', 0);
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', [
                'in' => [
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH,
                ]
            ])
            ->addAttributeToFilter([
                ['attribute' => 'name', 'like' => '%'.$queryText.'%'],
                ['attribute' => 'sku',  'like' => '%'.$queryText.'%']
            ])
            ->setPageSize(12);

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
}