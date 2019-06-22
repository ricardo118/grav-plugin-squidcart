<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Grav\Common\Language\Language;
use Grav\Common\Uri;
use Grav\Common\User\User;
use Grav\Common\Utils;
use RocketTheme\Toolbox\Session\Message;
use Stripe\Stripe;

/**
 * Class Controller
 * @package Grav\Plugin\Login
 */
class Controller
{
    /**
     * @var Grav
     */
    public $grav;

    /**
     * @var Stripe
     */
    public $stripe;

    /**
     * @var string
     */
    public $type;
    public $object;
    public $subaction;

    /**
     * @var array
     */
    protected $params;
    public $post;
    protected $config;

    /**
     * @var string
     */
    protected $redirect;

    /**
     * @var int
     */
    protected $redirectCode;

    /**
     * @var string
     */
    protected $prefix = 'task';

    /**
     * @param Stripe $stripe
     * @param string $type
     * @param string $object
     * @param string $subaction
     * @param array  $params
     * @param array  $post
     */
    public function __construct(Stripe $stripe, $type, $object, $subaction = null, $params = null, $post = null)
    {
        $this->grav = Grav::instance();
        $this->config = $this->grav['config']->get('plugins.squidcart');
        $this->stripe = $stripe;
        $this->type = $type;
        $this->object = $object;
        $this->subaction = $subaction;
        $this->params = $params;
        $this->post = $post ? $this->getPost($post) : [];
    }

    /**
     * Performs an action.
     * @throws \RuntimeException
     */
    public function execute()
    {
        $messages = $this->grav['messages'];

        // Set redirect if available.
        if (isset($this->post['_redirect'])) {
            $redirect = $this->post['_redirect'];
            unset($this->post['_redirect']);
        }

        $success = false;
        $method = $this->prefix . ucfirst($this->type);

        if (!method_exists($this, $method)) {
            throw new \RuntimeException($method, 404);
        }

        try {
            $success = call_user_func([$this, $method]);
        } catch (\RuntimeException $e) {
            $messages->add($e->getMessage(), 'error');
            $this->grav['log']->error('plugin.squidcart: '. $e->getMessage());
        }

        if (!$this->redirect && isset($redirect)) {
            $this->setRedirect($redirect, 303);
        }

        return $success;
    }

    /**
     * Redirects an action
     */
    public function redirect()
    {
        if ($this->redirect) {
            $this->grav->redirect($this->redirect, $this->redirectCode);
        }
    }

    /**
     * Set redirect.
     *
     * @param     $path
     * @param int $code
     */
    public function setRedirect($path, $code = 303)
    {
        $this->redirect = $path;
        $this->redirectCode = $code;
    }

    /**
     * Recursively JSON decode data.
     *
     * @param  array $data
     *
     * @return array
     */
    protected function jsonDecode(array $data)
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->jsonDecode($value);
            } else {
                $value = json_decode($value, true);
            }
        }

        return $data;
    }

    protected function taskDelete() {

        $messages = $this->grav['messages'];

        switch ($this->object)
        {
            case 'customers':
                $customers = new Customers($this->stripe, $this->config);

                if ($this->subaction)
                {
                    switch ($this->subaction) {
                        case 'sources':
                            try {
                                $customers->deleteSource($this->params['customers'], $this->params['card']);
                            } catch (\RuntimeException $e) {
                                $messages->add($e->getMessage(), 'error');
                                $this->grav['log']->error('plugin.squidcart: '. $e->getMessage());
                            }
//                            finally {
//                                $this->grav->redirect($this->redirect, $this->redirectCode);
//                            }
                            break;
                    }
                }
                break;
        }
    }


    // this function does nothing, currently just for keeping track of routes to be
    protected function adminRoutes()
    {
        $dashboard = [
            '/squidcart/dashboard'
        ];

        $products = [
            '/squidcart/products',
            '/squidcart/products/product:{product_id}',
            '/squidcart/products/product:{product_id}/update',
            '/squidcart/products/product:{product_id}/delete'
        ];

        $skus = [
            '/squidcart/skus',
            '/squidcart/skus/create',
            '/squidcart/skus/sku:{sku_id}',
            '/squidcart/skus/sku:{sku_id}/update',
            '/squidcart/skus/sku:{sku_id}/delete'
        ];

        $customers = [
            '/squidcart/customers',
            '/squidcart/customers/customer:{customer_id}'
        ];
    }
}
