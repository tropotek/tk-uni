<?php
namespace Uni;

use Tk\Event\Dispatcher;
use Tk\Controller\Resolver;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class FrontController extends \Bs\FrontController
{

    /**
     * @param Dispatcher $dispatcher
     * @param Resolver $resolver
     * @throws \Tk\Exception
     */
    public function __construct(Dispatcher $dispatcher, Resolver $resolver)
    {
        parent::__construct($dispatcher, $resolver);
    }

    /**
     * init Application front controller
     *
     * @throws \Tk\Exception
     */
    public function init()
    {
        parent::init();

        $dispatcher = $this->getDispatcher();

        $dispatcher->addSubscriber(new \Uni\Listener\InstitutionHandler());
    }

}