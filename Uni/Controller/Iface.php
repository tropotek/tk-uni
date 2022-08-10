<?php
namespace Uni\Controller;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Iface extends \Bs\Controller\Iface
{


    /**
     * @return \App\Db\Course|\Uni\Db\Course|\Uni\Db\CourseIface
     */
    public function getCourse()
    {
        return $this->getConfig()->getCourse();
    }

    /**
     * @return int
     */
    public function getCourseId()
    {
        return $this->getConfig()->getCourseId();
    }

    /**
     * @return \Uni\Db\Institution|\Uni\Db\InstitutionIface|null
     */
    public function getInstitution()
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
     * @return \App\Db\Subject|\Uni\Db\Subject|\Uni\Db\SubjectIface|null
     */
    public function getSubject()
    {
        return $this->getConfig()->getSubject();
    }

    /**
     * @return int|void
     */
    public function getSubjectId()
    {
        $this->getConfig()->getSubjectId();
    }

}