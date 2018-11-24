<?php
namespace Grav\Plugin\Squidcart\Twig;

use Grav\Common\Grav;
use Grav\Plugin\Squidcart;
use Grav\Plugin\Squidcart\Products;

class SquidcartTwigExtension extends \Twig_Extension
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

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('price', [$this, 'priceFilter'])
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getProduct', [$this, 'getProduct'])
        ];
    }

    public function priceFilter($n)
    {
        $currency = $this->configs['currency'];
        $number = $n/100;
        $number = number_format($number, $currency['decimals'], $currency['point'], $currency['separator']);

        if ($currency['position'] === 'right')
        {
            $number = $number . $currency['symbol'];
            return $number;
        }

        if ($currency['position'] === 'left')
        {
            $number = $currency['symbol'] . $number;
            return $number;
        }

        return $number; // without symbol
    }

    public function getProduct($id)
    {
        require_once __DIR__ . '/../Products.php';
        $products = new Products($this->stripe);
        $product = $products->getProduct([
            "id" => $id
        ]);

        return $product;
    }
}
