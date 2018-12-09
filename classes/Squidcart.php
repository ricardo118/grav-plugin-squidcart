<?php
namespace Grav\Plugin\Squidcart;

use Stripe\Stripe;

class Squidcart
{
    protected $keys;

    public function __construct($keys)
    {
        $this->keys = $keys;
    }

    public function init (): void
    {
        require_once __DIR__ . '/Orders.php';
        require_once __DIR__ . '/Products.php';
        require_once __DIR__ . '/Customers.php';
    }

    /**
     * Initialize the Stripe API and set the API Key Required.
     */
    public function initializeStripe(): Stripe
    {
        $stripe = new Stripe();
        $stripe::setApiKey($this->keys['secret']);

        return $stripe;
    }

    // this function does nothing, currently just for keeping track of routes to be
    public function adminRoutes()
    {
        $dashboard = [
            '/squidcart/dashboard'
        ];

        $products = [
            '/squidcart/products',
            '/squidcart/products/product:{product_id}',
            '/squidcart/products/product:{product_id}/update',
            '/squidcart/products/product:{product_id}/delete'
        ];

        $skus = [
            '/squidcart/skus',
            '/squidcart/skus/create',
            '/squidcart/skus/sku:{sku_id}',
            '/squidcart/skus/sku:{sku_id}/update',
            '/squidcart/skus/sku:{sku_id}/delete'
        ];

        $customers = [
            '/squidcart/customers',
            '/squidcart/customers/customer:{customer_id}'
        ];
    }
}
