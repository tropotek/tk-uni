<?php
namespace Uni\Listener;

use Tk\Event\Subscriber;
use Symfony\Component\HttpKernel\KernelEvents;


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
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     * @throws \Exception
     */
    public function onRequest(\Symfony\Component\HttpKernel\Event\RequestEvent $event)
    {
        $config = \Uni\Config::getInstance();
        /** @var \Uni\Db\User $user */
        $user = $config->getUser();
        if ($user && $user->getInstitution()) {
            $config->set('institution', $user->getInstitution());
        }
        if ($event->getRequest()->getAttribute('instHash')) {
            $institution = $config->getInstitutionMapper()->findByHash($event->getRequest()->getAttribute('instHash'));
            $config->set('institution', $institution);
        }

        // TODO: check if institution has a domain name and redirect if appropriate
        

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


