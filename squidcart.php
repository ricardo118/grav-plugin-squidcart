<?php
namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Common\Utils;
use Grav\Plugin\SquidCart\Controller;
use Grav\Plugin\SquidCart\Customers;
use Grav\Plugin\SquidCart\Orders;
use Grav\Plugin\SquidCart\Products;
use Grav\Plugin\Squidcart\Squidcart;
use Grav\Plugin\Squidcart\Twig\SquidcartTwigExtension;
use Stripe\Stripe;

/**
 * Class SquidCartPlugin
 * @package Grav\Plugin
 */
class SquidCartPlugin extends Plugin
{

    /**
     * @var Squidcart
     */
    protected $squidcart;

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var Customers
     */
    protected $customers;

    /**
     * @var Products
     */
    protected $products;

    /**
     * @var Stripe
     */
    protected $stripe;

    /**
     * @var array
     */
    protected $configs;
    protected $keys;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        $this->getConfigs();

        // Check we are setup
        if ($this->setup())
        {
            $this->initializeSquidcart();

            $this->enable([
                'onAssetsInitialized' => ['onAssetsInitialized', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onTwigTemplatePaths' => ['onTwigTemplatePaths' , 0],
                'onTwigExtensions'    => ['onTwigExtensions', 0]
            ]);

            if ($this->isAdmin())
            {
                $this->enable([
                    'onAdminMenu'        => ['onAdminMenu', 0]
                ]);
            }
//            $order = \Stripe\Order::retrieve('or_1Dwy5SGQJIwE4bvXwpnZo0z7');
//            $order->pay(['customer' => 'cus_EPngRK2ojm7CAa']);
        }
    }

    protected function setup()
    {
        $mode = $this->configs['mode'];

        if ($mode !== 'live' || $mode !== 'test')
        {
            $this->keys = $this->configs['keys'][$mode];
            if (isset($this->keys['public']) || isset($this->$keys['secret']))
            {
                return true;
            }

            $this->grav['messages']->add(sprintf($this->grav['language']->translate('PLUGIN_SQUIDCART.CONFIGURATION_REQUIRED'), $this->config->get('plugins.admin.route') . '/plugins/squidcart'), 'error');
            return false;
        }

    }

    /**
     * Initialize the Squidcart Class.
     */
    public function initializeSquidcart()
    {
        // Autoload classes
        require_once __DIR__ . '/vendor/autoload.php';
        require_once __DIR__ . '/classes/Squidcart.php';
        require_once __DIR__ . '/classes/Controller.php';

        // Initialize Squidcart and Stripe class.
        $this->squidcart = new Squidcart($this->keys);
        $this->squidcart->init();
        $this->stripe = $this->squidcart->initializeStripe();

        // Initialize
        $this->orders    = new Orders    ($this->stripe, $this->configs);
        $this->customers = new Customers ($this->stripe, $this->configs);
        $this->products  = new Products  ($this->stripe, $this->configs);
    }

    /**
     * Get Plugin configurations
     */
    public function getConfigs()
    {
        $this->configs = $this->config->get('plugins.squidcart');
    }

    public function onAssetsInitialized()
    {
        if ($this->isAdmin())
        {
            $this->grav['assets']->addCss('user/plugins/squidcart/admin/css-compiled/squidcart.css', 1);
            $this->grav['assets']->addJs('user/plugins/squidcart/js/Sortable.min.js');
            $this->grav['assets']->addJs('user/plugins/squidcart/js/admin.js', 1);
        }
    }

    // Twig related functions below
    public function onAdminMenu()
    {
        $this->grav['twig']->plugins_hooked_nav['Squidcart'] = ['route' => 'squidcart', 'icon' => ' fa-cc-stripe', 'authorize' => 'admin.squidcart'];
    }

    public function onTwigTemplatePaths()
    {
        if ($this->isAdmin())
        {
            $this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
        }

        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigExtensions()
    {
        require_once __DIR__ . '/classes/Twig/SquidcartTwigExtension.php';
        $this->grav['twig']->twig->addExtension(new SquidcartTwigExtension($this->configs, $this->stripe));
    }

    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];

        $twig->twig_vars['squidcart']['mode'] = $this->configs['mode'];
        $twig->twig_vars['squidcart']['currency'] = $this->configs['currency'];
        $twig->twig_vars['products'] = $this->products->getProducts();

        if ($this->isAdmin())
        {
            $twig->twig_vars['customers'] = $this->customers->getCustomers();
            $twig->twig_vars['orders'] = $this->orders->getOrders();
            $twig->twig_vars['dashboard'] = $this->squidcart->getDashboard();
        }
    }
}
