<?php

namespace Grav\Plugin\SquidCart;

use Grav\Common\Grav;
use Grav\Framework\Cache;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\Session\Message;

/**
 * Class Controller
 * @package Grav\Plugin\ShoppingCart
 */
class Controller
{
    private $grav;

    /**
     * @param Grav   $grav
     */
    public function __construct(Grav $grav)
    {
        $this->grav = Grav::instance();
    }

    /**
     * @param $url
     * @param $filename
     */
    public function addPage($url, $filename)
    {
        /** @var Pages $pages */
        $pages = $this->grav['pages'];
        $page = $pages->dispatch($url);

        if (!$page) {
            $page = new Page;
            $page->init(new \SplFileInfo(__DIR__ . '/pages/' . $filename));
            $page->slug(basename($url));
            $pages->addPage($page, $url);
        }

    }


}