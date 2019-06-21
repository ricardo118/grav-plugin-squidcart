<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Stripe\Coupon;
use Stripe\Error\Api;
use Stripe\Stripe;

class Coupons
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

    public function getCoupons(Array $opts = null)
    {
        $coupon = new Coupon();
        $data = $this->cache->fetch('squidcart_coupons');

        if(!$data) {
            try {
                $coupons = $coupon->all($opts);
                foreach ($coupons->autoPagingIterator() as $coupon) {
                    $data[] = $coupon;
                }
            } catch (Api $e) {
                dump($e);
            }
        }

        $this->cache->save('squidcart_coupons', $data, $this->expiration);
        return $data;
    }

    public function getCustomer(String $id)
    {
        $data = $this->getCustomers();
        $customer = [];

        foreach ($data as $item)
        {
            if ($item['id'] === $id) {
                $customer = $item;
                return $customer;
            }
        }
        return $customer;
    }
}
