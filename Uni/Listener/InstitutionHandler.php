<?php
namespace Uni\Listener;

use Tk\Event\Subscriber;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class InstitutionHandler implements Subscriber
{


    /**
     * Set the global institution into the config as a central data access point
     * If no institution is set then we know we are either an admin or public user...
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \Exception
     */
    public function onRequest($event)
    {
        $config = \Uni\Config::getInstance();
        /** @var \Uni\Db\User $user */
        $user = $config->getAuthUser();
        if ($user && $user->getInstitution()) {
            $config->set('institution', $user->getInstitution());
        }
        if ($event->getRequest()->attributes->get('instHash')) {
            $institution = $config->getInstitutionMapper()->findByHash($event->getRequest()->attributes->get('instHash'));
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


