<?php

namespace Custom\RelatedProducts\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Catalog\Api\ProductRepositoryInterface;
Use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Product\View as ProductView; // Get current product information

// Product Card
use Custom\ProductCard\Block\ProductCard;

// Exception
use Magento\Framework\Exception\NoSuchEntityException;

class RelatedProducts extends Template {
    private ProductRepositoryInterface $productRepository;
    private StoreManagerInterface $storeManager;
    private ProductView $productView;
    
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ProductView $productView,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productView = $productView;
        parent::__construct($context, $data);
    }

    /**
     * Get product block. This should be called in the .phtml
     * 
     * @param int $productId
     * @return null|string
     */
    public function getProductCard(int $productId): ?string
    {
        if (!$productId) return null;
        
        /** @var Template $block */
        $productCard = $this->getLayout()
            ->createBlock(ProductCard::class)
            ->setTemplate('Custom_ProductCard::product-card.phtml')
            ->setData('product_id', $productId)
            ->toHtml();

        if (!$productCard) return null;

        return $productCard;
    }

    /**
     * Get the last N related products for a given product (default 5)
     *
     * @param int $limit Max number of related products to return (default 5)
     * @param array $attributes Attributes to select (default selects common display fields)
     * @return Collection Returns a product collection; null if product not found
     */
    public function getRelatedProducts (
        int $limit = 4,
        array $attributes = ['name', 'small_image', 'thumbnail', 'url_key']
    ): Collection | array {
        $storeId   = (int)$this->storeManager->getStore()->getId();
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $currentProductId = $this->productView->getProduct()->getId();

        if (!$currentProductId) {
            return [];
        }

        try {
            /** @var Product $product */
            $product = $this->productRepository->getById($currentProductId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            return [];
        }

        $collection = $product->getRelatedProductCollection()
            ->addAttributeToSelect($attributes)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($storeId)
            ->addStoreFilter($storeId)
            ->addWebsiteFilter($websiteId)
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', [
                'in' => [
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_IN_SEARCH,
                    Visibility::VISIBILITY_BOTH,
                ],
            ])
            ->setOrder('created_at', 'DESC')
            ->setPageSize($limit)
            ->setCurPage(1);

        return $collection;
    }
}