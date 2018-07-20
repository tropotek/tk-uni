<?php
namespace Uni\Controller;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AdminManagerIface extends AdminIface
{
    use \Bs\Controller\ManagerTrait;

    /**
     * Get the currently logged in user
     *
     * @return \Uni\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     * @return \Tk\Config|\Uni\Config|\App\Config
     */
    public function getConfig()
    {
        return parent::getConfig();
    }

}