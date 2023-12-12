<?php
namespace Uni;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Dispatch extends \Bs\Dispatch
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        $dispatcher = $this->getDispatcher();

        $dispatcher->addSubscriber(new \Uni\Listener\InstitutionHandler());
        $dispatcher->addSubscriber(new \Uni\Listener\UserLogHandler());

        if ($this->getConfig()->get('site.mentors.enabled', true)) {
            $dispatcher->addSubscriber(new \Uni\Listener\MentorUpdateHandler());
        }

    }

}
