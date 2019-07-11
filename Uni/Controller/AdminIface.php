<?php
namespace Uni\Controller;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AdminIface extends \Bs\Controller\AdminIface
{

    /**
     * Get the currently logged in users institution object
     *
     * @return \Uni\Db\Institution|null|\Uni\Db\InstitutionIface
     */
    public function getSessionType()
    {
        return $this->getConfig()->getInstitution();
    }

    /**
     * @return int
     */
    public function getInstitutionId()
    {
        return $this->getConfig()->getInstitutionId();
    }

    /**
     * If the the current page is a subject page this wi;ll return the subject object
     * based on the subject code in the URI: /staff/VETS50001_2014_SM1/index.html
     *
     * @return \App\Db\Subject|\Uni\Db\Subject|null
     */
    public function getSubject()
    {
        return $this->getConfig()->getSubject();
    }

    /**
     * Returns the subject ID or 0 if none
     *
     * @return int
     */
    public function getSubjectId()
    {
        return $this->getConfig()->getSubjectId();
    }

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