<?php
namespace Uni\Controller;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PageResolver extends \Tk\Controller\Resolver
{

    /**
     * @param \Tk\Request $request
     * @return callable|false|object|Iface
     */
    public function getController(\Tk\Request $request)
    {
        $controller = parent::getController($request);

        /** @var Iface $controller */
        if (is_array($controller) && $controller[0] instanceof Iface) {
            $cObj = $controller[0];
            $page = \Uni\Config::getInstance()->createPage($cObj);
            $cObj->setPage($page);
            $request->setAttribute('controller.object', $cObj);
        }

        return $controller;
    }

}