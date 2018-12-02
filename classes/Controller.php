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

    public function taskDelete() {
        switch ($this->subaction) {
            case 'sku':
                    dump('test');
                    return 'ble';
                break;
            case 'product':
                break;
        }
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
     * @return array Array containing [redirect, code].
     */
    public function getRedirect()
    {
        return [$this->redirect, $this->redirectCode];
    }

    /**
     * Prepare and return POST data.
     *
     * @param array $post
     *
     * @return array
     */
    protected function &getPost(array $post)
    {
        unset($post[$this->prefix]);

        // Decode JSON encoded fields and merge them to data.
        if (isset($post['_json'])) {
            $post = array_merge_recursive($post, $this->jsonDecode($post['_json']));
            unset($post['_json']);
        }

        return $post;
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
}
