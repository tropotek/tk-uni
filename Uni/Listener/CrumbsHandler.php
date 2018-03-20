<?php
namespace Uni\Listener;

use Tk\Event\Subscriber;
use Tk\Kernel\KernelEvents;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class CrumbsHandler implements Subscriber
{

    /**
     * @param \Tk\Event\ControllerEvent $event
     * @throws \Tk\Exception
     */
    public function onController(\Tk\Event\ControllerEvent $event)
    {
        $crumbs = \Uni\Ui\Crumbs::getInstance();
        if (!$crumbs) throw new \Tk\Exception('Error creating crumb instance.');

        /** @var \Uni\Controller\Iface $controller */
        $controller = $event->getController();
        if ($controller instanceof \Uni\Controller\Iface) {
            // ignore adding crumbs if param in request URL
            if ($controller->getRequest()->has(\Uni\Ui\Crumbs::CRUMB_IGNORE)) {
                return;
            }
            $title = $controller->getPageTitle();
            if ($title == '') {
                $title = 'Dashboard';
            }
            $crumbs->trimByTitle($title);
            $crumbs->addCrumb($title, \Tk\Uri::create());
        }
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Tk\Exception
     */
    public function onShow(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        /** @var \Uni\Controller\Page $page */
        $page = $controller->getPage();

        if ($page instanceof \Uni\Controller\Page) {
            $crumbs = \Uni\Ui\Crumbs::getInstance();
            if (!$crumbs) return;

            $template = $page->getTemplate();
            $backUrl = $crumbs->getBackUrl();
            $js = <<<JS
config.backUrl = '$backUrl';
JS;
            $template->appendjs($js, array('data-jsl-priority' => '-999'));

            $js = <<<JS
jQuery(function($) {
  $('a.btn.back').attr('href', config.backUrl);
});
JS;
            $template->appendjs($js);
            if ($template->keyExists('var', 'breadcrumb')) {
                $template->replaceTemplate('breadcrumb', $crumbs->show());
                $template->setChoice('breadcrumb');
            }
        }
    }

    /**
     * @param \Tk\Event\RequestEvent $event
     */
    public function onFinishRequest(\Tk\Event\RequestEvent $event)
    {
        \Uni\Ui\Crumbs::save();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onController', 11),
            \Tk\PageEvents::CONTROLLER_SHOW =>  array('onShow', 0),
            KernelEvents::FINISH_REQUEST => 'onFinishRequest'
        );
    }

}