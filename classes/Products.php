<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Stripe\Error\Api;
use Stripe\Product;
use Stripe\SKU;
use Stripe\Stripe;

class Products
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

    public function getProducts(Array $opts = null)
    {
        $product = new Product();
        $data = $this->cache->fetch('squidcart_products');

        if(!$data) {
            try {
                $products = $product::all($opts);
                foreach ($products->autoPagingIterator() as $product) {
                    $data[] = $product;
                }
            } catch (Api $e) {
                dump($e->getJsonBody());
            }
        }

        $this->cache->save('squidcart_products', $data, $this->expiration);
        return $data;
    }


    public function getProduct(String $id, $getSkus = false)
    {
        $data = $this->getProducts();
        $product = [];
        $skus = [];

        foreach ($data as $item)
        {
            if ($item['id'] === $id) {
                $product = $item;
                if ($getSkus) {
                    foreach ($this->getSkus() as $sku)
                    {
                        if($sku['product'] === $id)
                        {
                            $skus[] = $sku;
                        }
                    }
                    $product['skus'] = $skus;
                    return $product;
                }
            }
        }
        return $product;
    }

    public function createProduct()
    {

    }

    public function updateProduct()
    {

    }

    public function deleteProduct()
    {

    }

    public function getSkus(Array $opts = null)
    {
        $stripe = new SKU();
        $data = $this->cache->fetch('squidcart_skus');

        if(!$data) {
            try {
                $skus = $stripe::all($opts);
                foreach ($skus->autoPagingIterator() as $sku) {
                    $data[] = $sku;
                }
            } catch (Api $e) {
                dump($e->getJsonBody());
            }
        }

        $this->cache->save('squidcart_skus', $data, $this->expiration);
        return $data;
    }

    public function createSku()
    {

    }

    public function updateSku()
    {

    }

    public function deleteSku($id)
    {
        $stripe = new SKU();
        try {
            dump($stripe->delete($id));
        } catch (Api $e) {
        }
    }
}
