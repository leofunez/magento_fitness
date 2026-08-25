<?php
namespace Custom\FeaturedProducts\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

// Get the category information
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\CategoryRepositoryInterface;

// Get attributes
use Magento\Eav\Model\Config as EavConfig;

// Product Card
use Custom\ProductCard\Block\ProductCard;

// Exception
use Magento\Framework\Exception\NoSuchEntityException;

class FeaturedProducts extends Template
{
    protected CollectionFactory $productCollectionFactory;
    protected $eavConfig;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
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
            // @intelephense-ignore-line
            // @intelephense-ignore-next-line
            ->setTemplate('Custom_ProductCard::product-card.phtml')
            ->setData('product_id', $productId)
            ->toHtml();

        if (!$productCard) return null;

        return $productCard;
    }

    /**
     * Get featured products collection
     */
    public function getFeaturedProducts(int $limit = 5)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'featured_product');

        if (!$attribute || !$attribute->getId()) {
            return [];
        }

        return $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('featured_product', 1)
            ->addAttributeToFilter('visibility', ['neq' => 1])
            ->addAttributeToFilter('status', 1)
            ->setPageSize($limit)
            ->setCurPage(1);
    }

    /**
     * Get last 5 products grouped by category IDs
     *
     * @param array $categoryIds
     * @param int $limit
     * @return array
     */
    public function getFeaturedProductsByCategory(array $categoryIds = [], int $limit = 5): array
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'featured_product');

        if (empty($categoryIds) || !$attribute || !$attribute->getId()) return [];

        $result = [];

        foreach ($categoryIds as $categoryId) {
            // Create collection for each category
            $collection = $this->productCollectionFactory->create();

            $collection->addAttributeToSelect('*')
                ->addCategoriesFilter(['in' => [$categoryId]])
                ->addAttributeToFilter('featured_product', 1)
                ->addAttributeToFilter('visibility', ['neq' => 1])
                ->addAttributeToFilter('status', 1)
                ->setOrder('created_at', 'DESC') // Get latest products
                ->setPageSize($limit)
                ->setCurPage(1);

            // Add to result array
            $result[$categoryId] = $collection;
        }

        return $result;
    }

    /**
     * Get category name by ID
     *
     * @param int $categoryId
     * @return string|null
     */
    public function getCategoryName(int $categoryId): ?string
    {
        try {
            /** @var CategoryRepositoryInterface $categoryRepository */
            $categoryRepository = ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
            $category = $categoryRepository->get($categoryId);
            return $category->getName();
        } catch (NoSuchEntityException $e) {
            return null; // Category not found
        }
    }
}
