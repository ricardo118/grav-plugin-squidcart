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

    public function init ()
    {
        require_once __DIR__ . '/Orders.php';
        require_once __DIR__ . '/Products.php';
        require_once __DIR__ . '/Customers.php';
    }

    /**
     * Initialize the Stripe API and set the API Key Required.
     */
    public function initializeStripe()
    {
        $stripe = new Stripe();
        $stripe->setApiKey($this->keys['secret']);

        return $stripe;
    }
}
