<?php

namespace Custom\ProductCard\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Review\Block\Product\ReviewRenderer;

class ProductCard extends Template {
    protected ProductRepositoryInterface $productRepository;
    protected ProductHelper $productHelper;
    protected Image $imageHelper;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductHelper $productHelper,
        Image $imageHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }


    /**
     * Get product URL
     */
    public function getProductUrl($product): string
    {
        return $this->productHelper->getProductUrl($product);
    }

    /**
     * Get product image URL
     */
    public function getImageUrl($product)
    {
        return $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }

    /**
     * Get price HTML for product
     */
    public function getPriceHtml($product)
    {
        /** @var Render $priceRender */
        $priceRender = $this->getLayout()->createBlock(
            Render::class,
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );

        if (!$priceRender) {
            return '';
        }

        return $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            ['display_minimal_price' => true]
        );
    }

    /**
     * Get color options for configurable products
     */
    public function getColorOptions($product)
    {
        if ($product->getTypeId() !== 'configurable') {
            return [];
        }

        $colors = [];
        $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($childProducts as $child) {
            $color = $child->getAttributeText('color');
            if ($color && !in_array($color, $colors)) {
                $colors[] = $color;
            }
        }

        return $colors;
    }

    /**
     * Return product block
     * @param int $productId
     */
    public function getProduct(int $productId) {
        try {
            return $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return null; // Product not found
        }
    }

    /**
     * Render the reviews summary HTML for a product.
     *
     * @param int $productId Product entity ID
     * @param string $templateType 'short' or 'default'
     * @param bool $displayIfNoReviews Whether to show placeholder if no reviews exist
     * @return string Rendered HTML or empty string if product not found
     */
    public function getReviews(
        int $productId,
        string $templateType = 'short',
        bool $displayIfNoReviews = false // true -> "Be the first to review this product"
    ): string {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return '';
        }

        /** @var ReviewRenderer $reviewBlock */
        $reviewBlock = $this->getLayout()->createBlock(ReviewRenderer::class);

        return $reviewBlock->getReviewsSummaryHtml($product, $templateType, $displayIfNoReviews);
    }
}