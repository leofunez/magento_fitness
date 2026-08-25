<?php

namespace Custom\ProductList\Controller\Ajax;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

use Custom\ProductList\Block\ProductList;
use Custom\ProductCard\Block\ProductCard;

class Load implements ActionInterface, HttpGetActionInterface
{
    private JsonFactory $resultJsonFactory;
    private RequestInterface $request;
    private PageFactory $pageFactory;
    private ProductList $productList;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        PageFactory $pageFactory,
        ProductList $productList
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->productList = $productList;
    }

    public function execute()
    {
        $pageSize   = 12;
        $page       = (int)    $this->request->getParam('current_page', 1);
        $categoryId = (int)    $this->request->getParam('category_id');
        $orderBy    = (string) $this->request->getParam('order');
        $direction  = (string) $this->request->getParam('direction');

        $products = $this->productList->getCategoryProducts(
            $page,
            $pageSize,
            $categoryId ?: 0,
            $orderBy,
            $direction
        );

        $html = '';

        if ($products->getSize() > 0) {
            $pageResult = $this->pageFactory->create();
            $layout = $pageResult->getLayout();

            foreach($products as $product) {
                $productBlock = $layout->createBlock(ProductCard::class)
                    ->setTemplate('Custom_ProductCard::product-card.phtml')
                    ->setData('product_id', $product->getId())
                    ->toHtml();

                $html .= $productBlock;
            }
        }

        return $this->resultJsonFactory->create()->setData([
            'success'    => true,
            'html'       => $html,
            'has_more'   => $products->getSize() >= $pageSize, // It should works, but it doesn't
            'total'      => $products->getSize(),
            'category_id'=> $categoryId,
            'page_size'  => $pageSize,
            'orderBy'    => $orderBy,
            'direction'  => $direction
        ]);
    }
}