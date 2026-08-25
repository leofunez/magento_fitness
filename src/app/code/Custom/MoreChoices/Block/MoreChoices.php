<?php
namespace Custom\MoreChoices\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

use Custom\ProductCard\Block\ProductCard;

class MoreChoices extends Template {
    protected CheckoutSession $checkoutSession;
    protected LinkFactory $linkFactory;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        LinkFactory $linkFactory,

        array $data = []
    ){
        $this->checkoutSession = $checkoutSession;
        $this->linkFactory = $linkFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get products for more choices
     *
     */
    public function getProducts()
    {
        $quote = $this->checkoutSession->getQuote();

        if(!$quote || !$quote->getItemsCount()) {
            return [];
        }

        $productIds = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductId()) {
                $productIds[] = (int) $item->getProductId();
            }
        }

        /** @var Link $link */
        $link = $this->linkFactory->create();
        $link->useCrossSellLinks();

        $collection = $link->getProductCollection()
            ->addProductFilter($productIds)
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter(
                'visibility',
                ['in' => [
                    Visibility::VISIBILITY_NOT_VISIBLE,
                    Visibility::VISIBILITY_BOTH
                ]]
            )
            ->setOrder('created_at', 'DESC')
            ->setPageSize(5)
            ->setCurPage(1);

        // Prevent duplicate product IDs
        $collection->getSelect()->distinct(true);
        $collection->getSelect()->group('e.entity_id');

        return $collection;
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
}