<h1 align="center">
    <a href="http://bitbag.shop" target="_blank">
        ![](doc/logo.jpeg | width=100)
    </a>
    <br />
    <a href="https://packagist.org/packages/bitbag/quadpay-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/bitbag/quadpay-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/bitbag/quadpay-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/bitbag/quadpay-plugin.svg" />
    </a>
    <a href="http://travis-ci.org/BitBagCommerce/SyliusQuadPayPlugin" title="Build status" target="_blank">
        <img src="https://img.shields.io/travis/BitBagCommerce/SyliusQuadPayPlugin/master.svg" />
    </a>
    <a href="https://scrutinizer-ci.com/g/BitBagCommerce/SyliusQuadPayPlugin/" title="Scrutinizer" target="_blank">
        <img src="https://img.shields.io/scrutinizer/g/BitBagCommerce/SyliusQuadPayPlugin.svg" />
    </a>
    <a href="https://packagist.org/packages/bitbag/quadpay-plugin" title="Total Downloads" target="_blank">
        <img src="https://poser.pugx.org/bitbag/quadpay-plugin/downloads" />
    </a>
</h1>

## Overview

This plugin allows you to integrate QuadPay payment with Sylius platform app.

## Support

We work on amazing eCommerce projects on top of Sylius and Pimcore. Need some help or additional resources for a project?
Write us an email on mikolaj.krol@bitbag.pl or visit [our website](https://bitbag.shop/)! :rocket:

## Installation

1. Require plugin with composer:

    ```bash
    composer require bitbag/quadpay-plugin
    ```

2. Import configuration:

    ```yaml
    imports:
        - { resource: "@BitBagSyliusQuadPayPlugin/Resources/config/config.yml" }
    ```

3. Add parameters to `config.yml`:

    ```yaml
    parameters:
        sylius.form.type.checkout_select_payment.validation_groups: ['sylius', 'checkout_select_payment']
    ```

4. Add plugin class to your `AppKernel`:

    ```php
    $bundles = [
        new \BitBag\SyliusQuadPayPlugin\BitBagSyliusQuadPayPlugin(),
    ];
    ```

5. Copy templates from `vendor/bitbag/quadpay-plugin/src/Resources/views/SyliusShopBundle/` 
   to `app/Resources/SyliusShopBundle/views/`.

6. Install assets:

    ```bash
    bin/console assets:install --symlink web
    ```

7. Clear cache:

    ```bash
    bin/console cache:clear
    ```

## Required merchant configuration in QuadPay

Merchant configuration must have `captureFundsOnOrderCreation` set to true.

## Cron job

Integrations should keep track of what orders have been sent to QuadPay for payment and have a scheduled job that runs every 10 minutes or so that checks the status of these orders.

For example:

```bash
*/10 * * * * bin/console bitbag:quadpay:update-payment-state
```

## QuadPay Widget

QuadPay Widget could be rendered in your twig templates using `bitbag_quadpay_render_widget([amount], [channel])` helper extension.

For example:

```twig
{% block stylesheets %}
    {{ parent() }}

    <link rel="stylesheet" href="{{ asset('bundles/bitbagsyliusquadpayplugin/css/style.css') }}">
{% endblock %}

{{ bitbag_quadpay_render_widget(cart.total, sylius.channel) }}
```

## Customization

### Available services you can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) and forms you can [extend](http://symfony.com/doc/current/form/create_form_type_extension.html)

Run the below command to see what Symfony services are shared with this plugin:
 
```bash
$ bin/console debug:container bitbag_sylius_quadpay_plugin
```

## Testing

```bash
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run gulp
$ bin/console assets:install web -e test
$ bin/console doctrine:database:create -e test
$ bin/console doctrine:schema:create -e test
$ bin/console server:run 127.0.0.1:8080 -d web -e test
$ open http://localhost:8080
$ bin/behat
$ bin/phpspec run
```

## Contribution

Learn more about our contribution workflow on http://docs.sylius.org/en/latest/contributing/.
