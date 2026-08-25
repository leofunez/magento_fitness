# THEMING


### Create theme folders
The folders should be located in `app/design/frontend/Custom/Blank`


### Create configuration files
#### 1. Create theme.xml file
In the root of Blank folder, create this file named theme.xml:

```xml
<theme xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:noNamespaceSchemaLocation="urn:magento:framework:Config/etc/theme.xsd">
    <title>Frontend Theme</title>
    <parent>Magento/blank</parent>

    <!--
    <media>
        <preview_image>media/preview.jpg</preview_image>
    </media>
    -->
</theme>
```


#### 2. Create registration.php file
In the root of Blank folder, create this file named registration.php:

```php
<?php

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::THEME,
    'frontend/Custom/Blank',
    __DIR__
);
```
Navigate to `Content/Themes` to see the new theme in the list.


### Activate new Custom/Blank theme
1. Navigate to `Content/Configuration`.
2. In the list click to Edit in the Global item.
3. In the Default Themes section, select the new theme in the Applied Theme dropdown.
4. Click on Save Configuration.
5. You should see the new theme in the frontend https://magento.test


### Assets files
1. Create the `web` folder inside `Custom/Blank`
2. Create `css/source, fonts, js`, and `images` folders inside `web`.


### LESS files
1. Create `_extend.less, _fonts.less, _colors.less, _mixins.less, _breakpoint.less and _custom.less` files in `css/source` folder.

2. Import these files in `_extend.less` file:

    `@import "./_fonts.less";`

    `@import "./_custom.less";`

3. Deploy static files:

    `bin/cli bin/magento setup:static-content:deploy -f`
    
    `bin/cli bin/magento setup:upgrade`


### Forders for custom .phtml templates
1. Create `Magento_Theme` folder inside `Custom/Blank`.
2. Create `layout` and `templates/html` folders inside `Magento_Theme`.


### Global layout xml file
Create `default.xml` file in `Magento_Theme/layout` folder. In this folder we're going to import the header and footer templates.

```xml

<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <head>
        <script src="js/main.js" />
    </head>

    <body>
        <referenceBlock name="logo" remove="true" />
        <referenceBlock name="header" remove="true" />

        <!-- Header -->
        <referenceContainer name="header.container" htmlClass="header">
            <!-- Sticky -->
            <container name="header.sticky" htmlTag="div" htmlClass="header__sticky">
                <block
                    name="header.sticky_container"
                    class="Magento\Framework\View\Element\Template"
                    template="Magento_Theme::html/header/header-sticky.phtml"
                >
                    <block 
                        class="Magento\Theme\Block\Html\Topmenu" 
                        name="catalog.topnav.secondary" 
                        template="Magento_Theme::html/main-menu.phtml" 
                        after="header.links" 
                    />

                    <block 
                        class="Magento\Checkout\Block\Cart\Sidebar" 
                        name="minicart.secondary" 
                        template="Magento_Theme::html/mini-cart.phtml" 
                        after="header.links" 
                    />
                </block>
            </container>
            <!-- .Sticky -->

            <!-- Top -->
            <container name="header.topLinks" htmlTag="div" htmlClass="header_top-links" />

            <container
                name="header.top"
                after="header.topLinks"
                htmlTag="div"
                htmlClass="header__top"
            >
                <block
                    name="header.banner"
                    class="Magento\Framework\View\Element\Template"
                    template="Magento_Theme::html/header/header-banner.phtml"
                />
            </container>
            <!-- .Top -->

            <!-- Middle -->
            <container
                name="header.middle"
                after="header.top"
                htmlTag="div"
                htmlClass="header__middle"
            >
                <container
                    name="header.wrapper"
                    htmlTag="div"
                    htmlClass="header__wrapper"
                >
                    <!-- Logo -->
                    <container
                        name="header.logo"
                        htmlClass="header__logo"
                        htmlTag="div"
                        before="-"
                    >
                        <block
                            name="custom_logo_block" class="Magento\Theme\Block\Html\Header\Logo"
                        >
                            <arguments>
                                <argument name="logo_file" xsi:type="string">images/logo.png</argument>
                                <argument name="logo_img_width" xsi:type="number">300</argument>
                                <argument name="logo_img_height" xsi:type="number">100</argument>
                            </arguments>
                        </block>
                    </container>
                    <!-- .Logo -->

                    <!-- Marketplace -->
                    <block
                        name="header.marketplace"
                        after="logo"
                        class="Magento\Framework\View\Element\Template"
                        template="Magento_Theme::html/header/header-text.phtml"
                    />
                    <!-- .Marketplace -->
                    
                    <!-- Search Cart -->
                    <container
                        name="header.search-cart"
                        after="header.marketplace"
                        htmlTag="div"
                        htmlClass="header__search"
                    />
                    <!-- .Search Cart -->
                </container>
            </container>
            <!-- .Middle -->
        </referenceContainer>

        <move element="header.links" destination="header.topLinks" before="-" />
        <move element="top.search" destination="header.search-cart" before="-"/>
        <move element="minicart" destination="header.search-cart" after="top.search"/>
        <!-- .Header -->

        <!-- Bottom -->
            <!-- Nav Menu -->
                <referenceBlock name="catalog.topnav">
                    <action method="setTemplate">
                        <argument name="template" xsi:type="string">Magento_Theme::html/main-menu.phtml</argument>
                    </action>
                </referenceBlock>
            <!-- .Nav Menu -->
        <!-- .Bottom -->

        <!-- Footer -->
        <referenceBlock name="footer" remove="true" />
        <referenceContainer name="footer-container">
            <block
                class="Magento\Framework\View\Element\Template"
                name="footer.custom"
                template="Magento_Theme::html/footer.phtml"
                before="footer"
            />
        </referenceContainer>

        <move element="copyright" destination="footer.custom" />
        <!-- .Footer -->
    </body>
</page>
```

Clean cache to see the changes:

`bin/cli bin/magento cache:flush`


### Catalog set up

1. Inside `Custom/Blank` folder, create `Magento_Catalog` folder
2. Create `catalog_category_view.xml` file.
3. The content of this file should be like:
```xml
<?xml version="1.0"?>
<page
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd"
    layout="1column"
>
    <body>
        <referenceBlock name="category.products.list">
            <action method="setTemplate">
                <argument
                    name="template"
                    xsi:type="string"
                >
                        Magento_Catalog::product/product-list.phtml
                </argument>
            </action>
        </referenceBlock>

        <move element="sidebar.main" destination="category.products.list" as="sidebar_main" before="-" />
        <move element="sidebar.additional" destination="category.products.list" as="sidebar_additional" before="-" />
        <move element="page.main.title" destination="category.products.list" as="category_name" before="-" />
        <move element="product_list_toolbar" destination="category.products.list" as="toolbar" before="page.main.title" />
    </body>
</page>

```

### Product templating
1. Inside `Magento_Catalog` folder, create `catalog_product_view.xml` file.
3. The content of this file should be like:

```xml
<?xml version="1.0"?>
<page layout="2columns-left" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="content">
            <container name="product.detail.wrapper" htmlTag="div" htmlClass="wrapper" before="-" />
        </referenceContainer>
        
        <block 
            class="Magento\Catalog\Block\Navigation" 
            name="product.aside.all.categories" 
            template="Magento_Catalog::navigation/all-categories.phtml" 
            cacheable="false"
        >
            <arguments>
                <argument name="root_id" xsi:type="number">2</argument> 
                <argument name="title" xsi:type="string">Categories</argument>
            </arguments>
        </block>

        <container name="product.detail.content" htmlTag="div" htmlClass="product-detail__content" before="-" />
        <referenceContainer name="product.detail.content">
            <block
                name="product.detail"
                class="Magento\Framework\View\Element\Template"
                template="Magento_Catalog::product/product-detail.phtml"
                before="-"
            />
        </referenceContainer>
        <move element="product.detail.content" destination="product.detail.wrapper" />

        <referenceContainer name="product.info.form.content">
            <block
                name="product.info.addtocart"
                class="Magento\Catalog\Block\Product\View" 
                template="Magento_Catalog::product/product-addtocart.phtml" 
                before="-"
            /> 
        </referenceContainer>

        <move element="product.aside.all.categories" destination="product.detail" as="product_categories"/>
        <move element="page.main.title" destination="product.detail" as="product_title" />
        <move element="product.info.media" destination="product.detail" as="product_media"/>
        <move element="product.info.main" destination="product.detail" as="product_info"/>
        <move element="product.info.options.wrapper" destination="product.detail" as="product_options" />
        
        <move element="product.info.addtocart" destination="product.detail" as="product_add_to_cart" />
        
        <move element="product.info.details" destination="product.detail" as="product_description"/>
        <move element="catalog.product.related" destination="product.detail" as="product_related"/>

        <referenceBlock name="product.info" remove="true" />
    </body>
</page>
```

4. Inside Magento_Catalog folder, create `templates/navigation` or `templates/product` folders. In these folder you should created the different templates files like: `product-addtocart.phml`, `product-detail.phtml` or `product-list.phtml`