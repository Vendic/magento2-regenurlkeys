## Magento 2 - regenerate missing url keys
In one of our projects we lost a lot of url_key entries, due to the uninstallation of a third party SEO module. This module wil find all the products without an url key and regenerates them using the product name as a basis.

### Installation
```bash
composer require vendic/magento2-regenurlkeys
```

### Usage
```bash
php bin/magento regenerate:product:urlkeys
```


### About Vendic
[Vendic - Magento 2](https://vendic.nl "Vendic Homepage") develops technically challenging e-commerce websites using Magento 2. Feel free to check out our projects on our website.
