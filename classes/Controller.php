<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Grav\Common\Language\Language;
use Grav\Common\Uri;
use Grav\Common\User\User;
use Grav\Common\Utils;
use RocketTheme\Toolbox\Session\Message;

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
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $subaction;

    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    public $post;

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
     * @param Grav   $grav
     * @param string $action
     * @param string $subaction
     * @param string $id
     * @param array  $post
     */
    public function __construct(Grav $grav, $action, $subaction, $id, $post = null)
    {
        $this->grav = $grav;
        $this->action = $action;
        $this->subaction = $subaction;
        $this->id = $id;
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
        $method = $this->prefix . ucfirst($this->action);

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

    /**
     * Initialize login controller
     */
    public function taskController()
    {
        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $task = !empty($_POST['task']) ? $_POST['task'] : $uri->param('task');
        $task = explode('.', $task);
        $post = !empty($_POST) ? $_POST : [];
        $action = $task[1];
        $subaction = $task[2] ? $task[2] : '';
        $id = !empty($_POST['id']) ? $_POST['id'] : $uri->param('id');

        switch ($task) {
            case 'delete.sku':
                break;
        }

        $controller = new Controller($this->grav, $action, $subaction, $id, $post);
        $controller->execute();
        $controller->redirect();
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
