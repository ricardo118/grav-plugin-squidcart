<?php
namespace Grav\Plugin;

use Grav\Common\Page\Page;
use Grav\Common\Plugin;
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
     * @var Stripe
     */
    protected $stripe;
    /**
     * @var Array
     */
    protected $configs;
    protected $keys;

    /**
     * @return array
     *
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths'  => ['onTwigTemplatePaths' , 0],
            'onTwigExtensions' => ['onTwigExtensions', 0],
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        $this->getConfigs();

        // Check we are setup
        if ($this->setup()) {

            $this->initializeSquidcart();

            $this->enable([
                'onAssetsInitialized' => ['onAssetsInitialized', 0]
            ]);

            if ($this->isAdmin()) {

                $this->enable([
                    'onAdminMenu' => ['onAdminMenu', 0]
                ]);
            }

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
    protected function initializeSquidcart()
    {
        // Autoload classes
        require_once __DIR__ . '/vendor/autoload.php';
        require_once __DIR__ . '/classes/Squidcart.php';

        $this->initializeStripe();

        // Initialize squidCart class.
        $this->squidcart = new Squidcart($this->grav, $this->stripe, $this->configs);

        $twig = $this->grav['twig'];
        $twig->twig_vars['squidcart']['mode'] = $this->configs['mode'];
        $twig->twig_vars['squidcart']['currency'] = $this->configs['currency'];
        $twig->twig_vars['products'] = $this->squidcart->getProducts();
        $twig->twig_vars['customers'] = $this->squidcart->getCustomers();
        $twig->twig_vars['orders'] = $this->squidcart->getOrders();

    }

    /**
     * Initialize the Stripe API and set the API Key Required.
     */
    protected function initializeStripe()
    {
        $this->stripe = new Stripe();
        $this->stripe->setApiKey($this->keys['secret']);
    }

    /**
     * Get Plugin configurations
     */
    public function getConfigs()
    {
        $this->configs = $this->config->get('plugins.squidcart');
    }

    /**
     * Add templates directory to twig lookup paths. We use this way of adding templates
     * rather than the scanTemplates method so they don't get added to the `Add Page` in admin
     */
    public function onTwigTemplatePaths()
    {
        if ($this->isAdmin()) {
            $this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
        }

        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigExtensions()
    {
        require_once __DIR__ . '/classes/Twig/SquidcartTwigExtension.php';
        $this->grav['twig']->twig->addExtension(new SquidcartTwigExtension($this->configs, $this->stripe));
    }

    public function onAdminMenu()
    {
        $this->grav['twig']->plugins_hooked_nav['Squidcart'] = ['route' => 'squidcart', 'icon' => ' fa-cc-stripe', 'authorize' => 'admin.squidcart'];
    }

    public function onAssetsInitialized()
    {
        if ($this->isAdmin()) {
            $this->grav['assets']->addCss('user/plugins/squidcart/admin/css-compiled/squidcart.css', 1);
            $this->grav['assets']->addJs('user/plugins/squidcart/js/admin.js', 1);
        }

    }

    public static function getProducts()
    {

    }

}
