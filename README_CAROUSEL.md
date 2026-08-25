# Hero Carousel

### Create folders
1. Create a HeroCarousel folder in `app/code/Custom`.
2. Create `etc/adminhtml` folder in `app/code/Custom/HeroCarousel`.


### Registration
This file is to register the module with Magento's component system.

Each custom module needs a `registration.php` file so Magento knows it exists. This file tells Magento where the module's code is located and associate the module's name with its directory path.

In general terms, this file registers the module in Magento so it can be recognized, loaded, and enabled.

In the root of HeroCarousel folder, create the `registration.php` file with this content:

```php
<?php
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Custom_HeroCarousel',
    __DIR__
);
```

### Module
This file defines the basic information of the module. It tells Magento the name of the module, version and dependencies on other modules.

In the root of the `HeroCarousel/etc` folder create a `module.xml` file with this content:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Custom_HeroCarousel" setup_version="1.0.0"/>
</config>
```




### System
It creates configuration options in the Magento Admin Panel.

This file defines sections, groups, and fields that appears under Store → Configuration. It allows admin users to manage settings for the module.

In the root of the `HeroCarousel/etc/adminhtml` folder create a `system.xml` file with this content:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Config:etc/system_file.xsd">

    <system>
        <section id="hero_carousel"
                 translate="label"
                 sortOrder="500"
                 showInDefault="1"
                 showInWebsite="1"
                 showInStore="1">
            <label>Hero Carousel</label>
            <tab>general</tab>
            <resource>Custom_HeroCarousel::config</resource>

            <group id="general"
                   translate="label"
                   sortOrder="10"
                   showInDefault="1"
                   showInWebsite="1"
                   showInStore="1">
                <label>Hero Carousel Settings</label>

                <field id="enable"
                       translate="label"
                       type="select"
                       sortOrder="10"
                       showInDefault="1"
                       showInWebsite="1"
                       showInStore="1">
                    <label>Enable Hero Carousel</label>
                    <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
                </field>
            </group>
        </section>
    </system>
</config>
```


### ACL
The purpose of this file is to controls user permissions for the module's configuration.

This file defines Access Control List rules - determining which admin roles can view or modofy the module's configuration. It connects to <resource> node inside `system.xml`.

In the root of the `HeroCarousel/etc` folder create a `acl.xml` file with this content:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Acl/etc/acl.xsd">

    <acl>
        <resources>
            <resource id="Magento_Backend::admin" title="Admin">
                <resource id="Magento_Backend::stores" title="Stores">
                    <resource id="Magento_Backend::stores_settings" title="Settings">
                        <resource id="Magento_Config::config" title="Configuration">
                            <resource id="Custom_HeroCarousel::config" title="Hero Carousel Config" sortOrder="90"/>
                        </resource>
                    </resource>
                </resource>
            </resource>
        </resources>
    </acl>
</config>
```

### Block Class
With this class we can get access to configuration values set in Magento.

We should create this file in `src/app/code/Custom/HeroCarousel/Block/Carousel.php`

```php
<?php
namespace Custom\HeroCarousel\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class Carousel extends Template
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

        for ($i = 1; $i <= 3; $i++) {
            $item = [
                'heading'  => $this->getConfig("hero_carousel/items/item{$i}/item{$i}_heading"),
                'text'     => $this->getConfig("hero_carousel/items/item{$i}/item{$i}_text"),
                'url'      => $this->getConfig("hero_carousel/items/item{$i}/item{$i}_url"),
                'cta'      => $this->getConfig("hero_carousel/items/item{$i}/item{$i}_cta"),
                'image'    => $this->getConfig("hero_carousel/items/item{$i}/item{$i}_image"),
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
```


### Connecting Block Class with the Frontend
After creating the Block Class we need to connect that with the frontend layout (.pthml).
This file tells Magento to insert the custom carousel block into the homepage layout.

This file should be created in `src/app/code/Custom/HeroCarousel/view/frontend/layout/cms_index_index.xml`.

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <block class="Custom\HeroCarousel\Block\Carousel"
               name="hero.carousel"
               template="Custom_HeroCarousel::hero-carousel.phtml"/>
    </body>
</page>
```

### TEMPLATE
This file is to render the data from the backend. We can access to getItems() and isEnabled() methods from the block class above and handle them in the template.

```php
<?php
/** @var $block \Custom\HeroCarousel\Block\Carousel */
$items = $block->getItems();

if ($block->isEnabled() && !empty($items)): ?>
<div class="hero-carousel">
    <?php foreach ($items as $item): ?>
        <?php if (!empty($item['image'])): ?>
            <?php $imageUrl = $block->getImageUrl($item['image']); ?>
            /// ... HTML TEMPLATE
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
```