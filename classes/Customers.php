<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Stripe\Customer;
use Stripe\Error\Card;
use Stripe\Error\InvalidRequest;
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

    /**
     * @param $id
     * @param $card
     * @return bool
     */
    public function deleteSource($id, $card)
    {
        $messages = $this->grav['messages'];
        $api = new Customer();

        try {
            $api::deleteSource($id, $card);
        } catch (Card $e) {
            $messages->add($e->getMessage(), 'error');
            $this->grav['log']->error('plugin.squidcart: '. $e->getMessage());
        }
        catch (InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err  = $body['error'];

            dump('Status is:' . $e->getHttpStatus() . "\n");
            dump('Type is:' . $err['type'] . "\n");
            dump('Code is:' . $err['code'] . "\n");
            // param is '' in this case
            dump('Param is:' . $err['param'] . "\n");
            dump('Message is:' . $err['message'] . "\n");
            $messages->add($e->getMessage(), 'error');
            $this->grav['log']->error('plugin.squidcart: '. $e->getMessage());
        }
        $this->clearCache();
        $this->getCustomers();

        return true;
    }

    protected function clearCache()
    {
        $this->cache->delete('squidcart_customers');
    }
}
