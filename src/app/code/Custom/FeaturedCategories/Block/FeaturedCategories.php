<?php
namespace Custom\FeaturedCategories\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Helper\Image;

class FeaturedCategories extends Template
{
    private $categoryCollectionFactory;
    protected Image $imageHelper;
    
    public function __construct(
        Template\Context $context,
        CollectionFactory $categoryCollectionFactory,
        Image $imageHelper,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get product image URL
     */
    public function getImageUrl($category)
    {   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $mediaBaseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
        return $mediaBaseUrl . str_replace('/media/', '' , $category->getImage());
    }

    /**
     * Fetches a filtered collection of categories.
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategories(array $categoryIds = [])
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('level', ['gt' => 1])
            ->addUrlRewriteToResult()
            ->addAttributeToFilter('image', ['notnull' => true]);

        if (!empty($categoryIds)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        }

        return $collection->load();
    }
}
