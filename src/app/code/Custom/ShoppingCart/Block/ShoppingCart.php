<?php

namespace Custom\ShoppingCart\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ShoppingCart extends Template {
    private CheckoutSession $checkoutSession;
    private Image $imageHelper;
    private PriceCurrencyInterface $priceCurrency;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        Context                    $context,
        CheckoutSession            $checkoutSession,
        Image                      $imageHelper,
        PriceCurrencyInterface     $priceCurrency,
        ProductRepositoryInterface $productRepository,

        array $data = []
    ){
        $this->checkoutSession          = $checkoutSession;
        $this->imageHelper              = $imageHelper;
        $this->priceCurrency            = $priceCurrency;
        $this->productRepository        = $productRepository;

        parent::__construct($context, $data);
    }

    /**
     * Get current quote
     * @return CartInterface
     */
    public function getQuote(): CartInterface
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Get all visible items in cart
     * 
     * @return Item[]
     */
    public function getProducts(): array
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get the product URL
     * @param Item $item
     * @return string
     */
    public function getProductUrl(Item $item): string
    {
        $product = $item->getProduct();

        if(!$product || !$product->getId()) return '';

        return $product->getProductUrl();
    }

    /**
     * Get the product image url
     * 
     * @param Item $item
     * @return string
     */
    public function getItemImage(Item $item): string
    {
        $product = $item->getProduct();

        return $this->imageHelper->init($product, 'cart_page_product_thumbnail')->getUrl();
    }

    /**
     * Get product options
     * 
     * @param Item $item
     * @return array
     */
    public function getItemOptions(Item $item): array
    {
        $product = $item->getProduct();

        if (!$product) return [];

        $options = $product->getTypeInstance()->getOrderOptions($item->getProduct());

        // Normalize the main option types
        $result = [];

        // Configurable / Attributes
        if (isset($options['attributes_info'])) {
            $result['attributes'] = $options['attributes_info'];
        }

        // Custom product options
        if (isset($options['options'])) {
            $result['custom_options'] = $options['options'];
        }

        // Additional product options
        if (isset($options['additional_options'])) {
            $result['additional_options'] = $options['additional_options'];
        }

        // Buy request parameters
        if (isset($options['info_buyRequest'])) {
            $result['buy_request'] = $options['info_buyRequest'];
        }

        return $result;
    }

    /**
     * Format price according to store currency & locale
     * 
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return $this->priceCurrency->format($price, false);
    }

    /**
     * Get original price without discounts
     * 
     * @param Item $item
     * @return float
     */
    public function getOriginalPrice(Item $item): float
    {
        $original = (float) $item->getOriginalPrice();

        if ($original > 0) return $original;

        $product = $item->getProduct();

        if (!$product || !$product->getId()) return 0.0;

        return (float) $product->getPrice();
    }

    /**
     * Get the final price with discount
     * 
     * @param Item $item
     * @return float
     */
    public function getFinalPrice(Item $item): float
    {
        return (float) $item->getPrice();
    }

    /**
     * Get product remove url
     * 
     * @param Item $item
     * @return string
     */
    public function getItemRemoveUrl(Item $item): string
    {
        return $this->getUrl(
            'checkout/cart/delete',
            ['id' => $item->getItemId()]
        );
    }

    /**
     * Get product edit url
     * 
     * @param Item $item
     * @return string
     */
    public function getItemEditUrl(Item $item): string
    {
        return $this->getUrl(
            'checkout/cart/configure',
            ['id' => $item->getItemId()]
        );
    }

    /**
     * Get summary information
     */
    public function getSummary(): array
    {
        $quote = $this->getQuote();

        return [
            'subtotal'   => $quote->getSubtotal(),
            'grandTotal' => $quote->getGrandTotal(),
            'couponCode' => $quote->getCouponCode(),

            'itemsCount' => $quote->getItemsCount(),
            'itemsQty'   => $quote->getItemsQty(),
        ];
    }
}