<?php
namespace Custom\Module\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryData implements ArgumentInterface
{
    private $categoryCollectionFactory;

    public function __construct(
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Fetches a filtered collection of categories.
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategories(array $categoryIds = [])
    {
        $collection = $this->categoryCollectionFactory->create()
            // ->addAttributeToSelect(['name', 'url_path', 'image', 'parent_id']) 
            ->addAttributeToSelect('*') 
            ->addAttributeToFilter('is_active', ['eq' => 1])
            ->addAttributeToFilter('level', ['gt' => 1]) // Exclude root
            ->addUrlRewriteToResult() // Ensure URLs are loaded efficiently
            ->addAttributeToFilter('image', ['notnull' => true]);

        if (!empty($categoryIds)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        }

        return $collection->load();
    }
}