<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface InstitutionIface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getOwnerId();

    /**
     * @return UserIface
     */
    public function getOwner();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return string
     */
    public function getHash();

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @param string $courseCode
     * @return CourseIface
     * @todo: \App\Db\CourseMap::create()->findByCode($courseCode, self::getInstitution()->getId());
     */
    public function findCourseByCode($courseCode);

    /**
     * @param int $courseId
     * @return CourseIface
     * @todo: \App\Db\CourseMap::create()->find(self::getRequest()->get('courseId'));
     */
    public function findCourse($courseId);

}