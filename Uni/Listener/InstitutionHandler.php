<?php
namespace Uni\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;
use Tk\Event\GetResponseEvent;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class InstitutionHandler implements Subscriber
{


    /**
     * Set the global institution into the config as a central data access point
     * If no institution is set then we know we are either an admin or public user...
     *
     * @param GetResponseEvent $event
     * @throws \Tk\Exception
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Db\Exception
     */
    public function onRequest(GetResponseEvent $event)
    {
        $config = \Uni\Config::getInstance();
        /** @var \Uni\Db\User $user */
        $user = $config->getUser();
        if ($user && $user->getInstitution()) {
            $config->setInstitution($user->getInstitution());
        }
        if ($event->getRequest()->getAttribute('instHash')) {
            $institution = $config->getInstitutionMapper()->findByHash($event->getRequest()->getAttribute('instHash'));
            $config->setInstitution($institution);
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
            KernelEvents::REQUEST => array('onRequest', -1)
        );
    }
}


