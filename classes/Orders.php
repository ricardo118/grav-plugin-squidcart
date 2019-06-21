<?php

namespace Grav\Plugin\SquidCart;


use Grav\Common\Grav;
use Stripe\Error\Api;
use Stripe\Order;
use Stripe\Stripe;

class Orders
{

    /**
     * @var Stripe
     */
    protected $stripe;

    protected $cache;
    protected $grav;
    protected $expiration;

    /**
     * @param Stripe $stripe
     * @param $configs
     */
    public function __construct(Stripe $stripe, $configs)
    {
        $this->grav = Grav::instance();
        $this->stripe = $stripe;
        $this->cache  = $this->grav['cache'];
        $this->expiration = $configs['expiration'];
    }

    public function getOrders(Array $opts = null)
    {
        $order = new Order();
        $data = $this->cache->fetch('squidcart_orders');

        if(!$data) {
            try {
                $orders = $order->all($opts);
                foreach ($orders->autoPagingIterator() as $order) {
                    $data[] = $order;
                }
            } catch (Api $e) {
                dump($e);
            }
        } else {
            $this->grav['debugger']->addMessage('Orders cache hit.');
        }

        $this->cache->save('squidcart_orders', $data, $this->expiration);
        return $data;
    }
}
