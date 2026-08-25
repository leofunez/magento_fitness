<?php

namespace Custom\ProductList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

use Magento\Framework\App\RequestInterface;

class ProductList extends Template
{   
    /** @var Collection|null */
    private $collection = null;

    public function __construct(
        Context $context,
        private CollectionFactory $collectionFactory,
        private CategoryRepositoryInterface $categoryRepository,
        private RequestInterface $request,
        array $data = []
    ){
        $this->request = $request;
        return parent::__construct($context, $data);
    }

    public function getCategoryProducts(
        int $page, 
        int $pageSize,
        int $categoryId,
        string $orderBy,
        string $direction
    ) {
        $category = $this->categoryRepository->get($categoryId);

        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE])
            ->addCategoryFilter($category)
            ->setPageSize($pageSize)
            ->setCurPage($page);
        
        if (in_array($orderBy, ['name', 'price', 'position'], true)) {
            $collection->addAttributeToSort($orderBy, ($direction === 'desc' ? 'DESC' : 'ASC'));
        } else {
            $collection->addAttributeToSort('position', 'ASC');
        }
        
        $this->collection = $collection;

        return $this->collection;
    }

    public function getCategoryId()
    {
        $categoryId = (int) $this->request->getParam('id');
        return $categoryId;
    }
}