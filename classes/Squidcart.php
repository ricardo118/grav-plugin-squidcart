<?php
namespace Grav\Plugin\Squidcart;

use Grav\Common\Cache;
use Grav\Common\Grav;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Error\Api;
use Stripe\Order;
use Stripe\Product;
use Stripe\Stripe;

class Squidcart
{
    /**
     * @var Grav
     */
    public $grav;
    /**
     * @var Stripe
     */
    public $stripe;
    /**
     * @var string
     */
    public $uri;

    private $limit;
    private $cache;
    private $expiration;

    /**
     * Constructor.
     *
     * @param Grav $grav
     * @param Stripe $stripe
     * @param $configs
     */
    public function __construct(Grav $grav, Stripe $stripe, $configs)
    {
        $this->grav   = $grav;
        $this->stripe = $stripe;
        $this->uri    = $this->grav['uri'];
        $this->cache  = $this->grav['cache'];
        $this->limit  = $configs['limit'];
        $this->expiration = $configs['expiration'];

    }

    public function getProducts(Array $opts = null)
    {
        $product = new Product();
        $data = $this->cache->fetch('squidcart_products');

        if(!$data) {
            try {
                $products = $product->all(['limit' => $this->limit]);
                foreach ($products->autoPagingIterator() as $product) {
                    $data[] = $product;
                }
            } catch (Api $e) {
                dump($e);
            }
        }

        $this->cache->save('squidcart_products', $data, $this->expiration);
        return $data;
    }

    public function getCustomers(Array $opts = null)
    {
        $customer = new Customer();
        $data = $this->cache->fetch('squidcart_customers');

        if(!$data) {
            try {
                $customers = $customer->all(['limit' => $this->limit]);
                foreach ($customers->autoPagingIterator() as $customer) {
                    $data[] = $customer;
                }
            } catch (Api $e) {
                dump($e);
            }
        }

        $this->cache->save('squidcart_customers', $data, $this->expiration);
        return $data;
    }

    public function getOrders(Array $opts = null)
    {
        $order = new Order();
        $data = $this->cache->fetch('squidcart_orders');

        if(!$data) {
            try {
                $orders = $order->all(['limit' => $this->limit]);
                foreach ($orders->autoPagingIterator() as $order) {
                    $data[] = $order;
                }
            } catch (Api $e) {
                dump($e);
            }
        }

        $this->cache->save('squidcart_orders', $data, $this->expiration);
        return $data;
    }
}
