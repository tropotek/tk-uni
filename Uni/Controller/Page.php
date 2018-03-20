<?php
namespace Uni\Controller;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Page extends \Tk\Controller\Page
{

    /**
     * Set the page heading, should be set from main controller
     *
     * @return \Dom\Template
     * @throws \Dom\Exception
     * @throws \Tk\Exception
     */
    public function show()
    {
        $template = parent::show();

        if ($this->getUser()) {
            $template->insertText('username', $this->getUser()->getName());
            $template->setAttr('dashUrl', 'href', \Uni\Uri::createHomeUrl('/index.html'));
// TODO
//            if ($this->getUser()->isStudent() && $this->getUser()->getInstitution() && $this->getUser()->getInstitution()->logo) {
//                $template->setAttr('logo-img', 'src', $this->getUser()->getInstitution()->getLogoUrl());
//            }

            $template->setChoice('logout');
        } else {
            $template->setChoice('login');
        }

        if (\Tk\AlertCollection::hasMessages()) {
            $template->insertTemplate('alerts', \Tk\AlertCollection::getInstance()->show());
            $template->setChoice('alerts');
        }

        if ($this->getUser()) {
            $institutionId = $this->getConfig()->getInstitutionId();
            $subjectId = $this->getConfig()->getSubjectId();
            $role = $this->getUser()->getRole();
            $js = <<<JS
config.subjectId = $subjectId;
config.institutionId = $institutionId;
config.role = '$role';
JS;
            $template->appendJs($js, array('data-jsl-priority' => -1000));
        }

        return $template;
    }

    /**
     * Get the currently logged in user
     *
     * @return \Uni\Db\UserIface
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     * Get the global config object.
     *
     * @return \Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }

}