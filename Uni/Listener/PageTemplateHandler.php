<?php
namespace Uni\Listener;


/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler extends \Bs\Listener\PageTemplateHandler
{

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function showPage(\Tk\Event\Event $event)
    {
        parent::showPage($event);
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            $page = $controller->getPage();
            if (!$page) return;

            $template = $page->getTemplate();
            /** @var \Uni\Db\User $user */
            $user = $controller->getUser();

            // Add anything to the page template here ...
            if ($user) {
                $institutionId = $this->getConfig()->getInstitutionId();
                $subjectId = $this->getConfig()->getSubjectId();
                $js = <<<JS
config.subjectId = $subjectId;
config.institutionId = $institutionId; 
JS;
                $template->appendJs($js, array('data-jsl-priority' => -1000));
            }

            /* @var \DOMElement[] $nodes */
            $nodes = $template->get('default-url');
            foreach ($nodes as $node) {
                $node->setAttribute('href', \Uni\Uri::createDefaultUrl($node->getAttribute('href')));
            }

            // get the unimelb login url
            /** @var \Uni\Db\Institution $inst */
            $inst = $this->getConfig()->getInstitutionMapper()->find(1);
            if ($inst) {
                $template->setAttr('institution-login', 'href', $inst->getLoginUrl());
            }
            $template->setAttr('client-login', 'href', \Uni\Uri::createDefaultUrl('/login.html'));

        }
    }

    /**
     * @return \Uni\Config|\Tk\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }

}