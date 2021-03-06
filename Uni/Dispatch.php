<?php
namespace Uni;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Dispatch extends \Bs\Dispatch
{


    /**
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function init()
    {
        parent::init();

        $dispatcher = $this->getDispatcher();

        $dispatcher->addSubscriber(new \Uni\Listener\InstitutionHandler());

    }

}