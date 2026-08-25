<?php
namespace Custom\HeroCarousel\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class HeroCarousel extends Template
{
    protected $scopeConfig;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Check if the carousel feature is enabled in store configuration
     */
    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            'hero_carousel/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve configured carousel items
     */
    public function getItems(): array
    {
        $items = [];
        $itemPath = 'hero_carousel/items/item';

        for ($i = 1; $i <= 3; $i++) {
            $item = [
                'heading'  => $this->getConfig("{$itemPath}{$i}/item{$i}_heading"),
                'text'     => $this->getConfig("{$itemPath}{$i}/item{$i}_text"),
                'url'      => $this->getConfig("{$itemPath}{$i}/item{$i}_url"),
                'cta'      => $this->getConfig("{$itemPath}{$i}/item{$i}_cta"),
                'image'    => $this->getConfig("{$itemPath}{$i}/item{$i}_image"),
            ];

            if (array_filter($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Helper to fetch config values
     */
    protected function getConfig(string $path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return the store media base URL
     */
    public function getMediaBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Build the full image URL for a carousel item
     */
    public function getImageUrl(?string $imageName): ?string
    {
        if (empty($imageName)) {
            return null;
        }
        return $this->getMediaBaseUrl() . 'hero_carousel/' . ltrim($imageName, '/');
    }
}
