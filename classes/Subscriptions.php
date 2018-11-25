<?php
/**
 * Created by PhpStorm.
 * User: Ricardo
 * Date: 13/10/2018
 * Time: 22:55
 */

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Stripe\Stripe;

class Subscriptions
{
    private $grav;

    /**
     * @param Grav   $grav
     */
    public function __construct(Grav $grav)
    {
        $this->grav = Grav::instance();
    }

    public function get() {

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

}
