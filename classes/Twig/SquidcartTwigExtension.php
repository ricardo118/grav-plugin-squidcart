<?php
namespace Grav\Plugin\Squidcart\Twig;

use Grav\Common\Grav;
use Grav\Plugin\SquidCart\Customers;
use Grav\Plugin\Squidcart\Products;
use Stripe\StripeObject;
use Stripe\Util\Util;
use Twig_Extension;

class SquidcartTwigExtension extends Twig_Extension
{
    /**
     * @var Grav
     */
    protected $grav;

    protected $configs;
    protected $stripe;

    public function __construct($configs, $stripe)
    {
        $this->grav = Grav::instance();
        $this->configs = $configs;
        $this->stripe = $stripe;
    }

    /**
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters() : array
    {
        return [
            new \Twig_SimpleFilter('price', [$this, 'priceFilter']),
            new \Twig_SimpleFilter('sArray', [$this, 'stripeObjectToArray'])
        ];
    }

    public function getFunctions() : array
    {
        return [
            new \Twig_SimpleFunction('getProduct', [$this, 'getProduct']),
            new \Twig_SimpleFunction('getCustomer', [$this, 'getCustomer'])
        ];
    }

    public function priceFilter($n)
    {
        $currency = $this->configs['currency'];
        $number = $n/100;
        $number = number_format($number, $currency['decimals'], $currency['point'], $currency['separator']);

        if ($currency['position'] === 'right')
        {
            $number .= $currency['symbol'];
            return $number;
        }

        if ($currency['position'] === 'left')
        {
            $number = $currency['symbol'] . $number;
            return $number;
        }

        return $number; // without symbol
    }

    public function getProduct($id, $skus = false)
    {
        require_once __DIR__ . '/../Products.php';
        $products = new Products($this->stripe, $this->configs);
        return $products->getProduct($id, $skus);
    }

    public function getCustomer($id)
    {
        require_once __DIR__ . '/../Customers.php';
        $customers = new Customers($this->stripe, $this->configs);
        return $customers->getCustomer($id);
    }

    /**
     * @param StripeObject $object
     * @return array
     */
    public function stripeObjectToArray($object) {

        return $object->__toArray(true);
    }
}
