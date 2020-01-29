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
     * @return \Uni\Db\InstitutionIface
     */
    public function getInstitution();

    /**
     * @return int
     */
    public function getInstitutionId();

    /**
     * Returns true if the user is enrolled fully into the subject
     *
     * @param int $subjectId
     * @return bool
     * @throws \Exception
     */
    public function isEnrolled($subjectId);



}
