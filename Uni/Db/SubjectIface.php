<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface SubjectIface extends \Tk\Db\ModelInterface, \Tk\ValidInterface
{


    /**
     * @return int
     */
    public function getAssessmentId();

    /**
     * @return InstitutionIface
     */
    public function getInstitution();

    /**
     * @return int
     */
    public function getCourseId();

    /**
     * @return CourseIface
     */
    public function getCourse();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return \DateTime
     */
    public function getDateStart();

    /**
     * @return \DateTime
     */
    public function getDateEnd();

    /**
     * If false, the student should be denied access to creating new submissions of any type for that subject.
     * If false, the UI should display historic grades and placement data.
     *
     * NOTE: generally this returns false when the current date is outside the start and end subject dates
     *
     * @return bool
     */
    public function isActive();

    /**
     * if set to false the no email notifications should be sent for this subject
     *
     * @return bool
     */
    public function isNotify();

    /**
     * If false, students will not be able to access/view this subject and its or their data.
     *
     * @return bool
     */
    public function isPublish();

}