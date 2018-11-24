<?php
/**
 * Created by PhpStorm.
 * User: Ricardo
 * Date: 13/10/2018
 * Time: 22:56
 */

namespace Grav\Plugin\SquidCart;


use Stripe\Error\Api;
use Stripe\Plan;
use Stripe\Product;
use Stripe\Stripe;

class Products
{

    /**
     * @var Stripe
     */
    protected $stripe;

    /**
     * @param Stripe $stripe
     */
    public function __construct(Stripe $stripe)
    {
        $this->stripe = $stripe;
    }

    public function getProducts(String $opts)
    {
        $product = new Product();
        try {
            $result = $product->all($opts)->data;
        } catch (Api $e) {
            dump($e->getJsonBody());
        }

        return $result;
    }

    public function filterByType(String $filter)
    {
        $result = [];

        foreach ($this->products as $product) {
            if($product->type === $filter){
                array_push($result, $product);
            }
        }

        return $result;
    }

    public function getPlans() {

        $plan = new Plan();

        try {
            $plans = $plan->all()->data;
            return $plans;
        } catch (Api $e) { }

    }

    public function matchPlansToProducts($plans, $products)
    {
        for ($x = 0; $x < count($products); $x++) {
            $tempPlans = [];
            foreach ($plans as $plan) {
                if($plan->product === $products[$x]->id) {
                    array_push($tempPlans, $plan);
                }
            }
            $products[$x]['plans'] = $tempPlans;
        }

        return $products;
    }

    public static function getSKUByAttribute($skus,$attribute, $value)
    {
        $result = [];
        foreach ($skus as $sku) {
            if($sku->attributes->$attribute === $value) {

                array_push($result, $sku);
            }
        }
        return $result;
    }


    public function getProduct($opts)
    {
        $product = new Product();
        return $product->retrieve($opts);
    }
}
