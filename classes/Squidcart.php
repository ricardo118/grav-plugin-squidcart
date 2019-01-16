<?php
namespace Grav\Plugin\Squidcart;

use Stripe\Balance;
use Stripe\Error\Api;
use Stripe\Payout;
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

    public function getDashboard()
    {

        $dashboard = [];
        $dashboard['balance'] = $this->getBalance();
        $dashboard['payout'] = $this->getPayout();

        return $dashboard;
    }

    protected function getBalance()
    {
        $stripe = new Balance();
        return $stripe::retrieve()->__toArray();
    }

    protected function getPayout()
    {
        $stripe = new Payout();
        try {
            return $stripe::all();
        } catch (Api $e) {

        }
    }

}
