<?php
namespace Uni\Controller\Admin;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Settings extends \Bs\Controller\Admin\Settings
{

    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Plugins',
            \Uni\Uri::createHomeUrl('/plugins.html'), 'fa fa-plug'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Admin Users',
            \Uni\Uri::createHomeUrl('/adminUserManager.html'), 'fa fa-users'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Institutions',
            \Uni\Uri::createHomeUrl('/institutionManager.html'), 'fa fa-institution'));

        if ($this->getConfig()->isDebug()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Roles {D}',
                \Bs\Uri::createHomeUrl('/roleManager.html'), 'fa fa-group'));
        }
    }

}