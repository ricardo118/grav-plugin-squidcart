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
}