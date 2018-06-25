<?php
namespace Uni\Listener;

use Tk\Event\Subscriber;

/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ActionPanelHandler implements Subscriber
{

    public function onShow(\Tk\Event\Event $event)
    {
        /** @var \Uni\Controller\AdminIface $controller */
        $controller = $event->get('controller');
        if ($controller instanceof \Uni\Controller\AdminIface && $controller->getActionPanel()) {
            $controller->getTemplate()->prependTemplate($controller->getTemplate()->getRootElement(), $controller->getActionPanel()->show());
        }
    }
    
    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::CONTROLLER_SHOW =>  array('onShow', 0)
        );
    }

}