<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface UserIface extends \Bs\Db\UserIface
{

    /**
     * This is the institution assigned staff/student number
     * @return int
     */
    public function getUid();

    /**
     * @return null|\Tk\Uri
     */
    public function getImageUrl();

    /**
     * @return null|InstitutionIface
     */
    public function getInstitution();

}