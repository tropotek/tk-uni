<?php
namespace Uni\Page;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Tk\Controller\Page
{


    /**
     * Set the page heading, should be set from main controller
     *
     * @return \Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = parent::show();

        if (\Tk\AlertCollection::hasMessages()) {
            $template->insertTemplate('alerts', \Tk\AlertCollection::getInstance()->show());
            $template->setChoice('alerts');
        }

        if ($this->getUser()) {
            $template->insertText('username', $this->getUser()->getDisplayName());
            $template->setAttr('dashUrl', 'href', \Uni\Uri::createHomeUrl('/index.html'));
// TODO
//            if ($this->getUser()->isStudent() && $this->getUser()->getInstitution() && $this->getUser()->getInstitution()->logo) {
//                $template->setAttr('logo-img', 'src', $this->getUser()->getInstitution()->getLogoUrl());
//            }

            $template->setChoice('logout');
        } else {
            $template->setChoice('login');
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

}