<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Stripe\Customer;
use Stripe\Error\Api;
use Stripe\Stripe;

class Customers
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

    public function getCustomers(Array $opts = null)
    {
        $customer = new Customer();
        $data = $this->cache->fetch('squidcart_customers');

        if(!$data) {
            try {
                $customers = $customer->all($opts);
                foreach ($customers->autoPagingIterator() as $customer) {
//                    $data[] = $customer->__toArray(true); // was testing using arrays only, works but has other issues
                    $data[] = $customer;
                }
            } catch (Api $e) {
                dump($e);
            }
        } else {
            $this->grav['debugger']->addMessage('Customers cache hit.');
        }

        $this->cache->save('squidcart_customers', $data, $this->expiration);
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

    public function deleteSource($id, $card)
    {
        $api = new Customer();
        $api::deleteSource($id, $card);
        $this->clearCache();
        $this->getCustomers();

        return true;
    }

    protected function clearCache()
    {
        $this->cache->delete('squidcart_customers');
    }
}
