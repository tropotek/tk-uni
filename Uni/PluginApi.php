<?php

namespace Uni;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface PluginApi
{

    /**
     * @param $username
     * @param $institutionId
     * @return null|\Tk\Db\Map\Model|Db\UserIface
     */
    public function findUser($username, $institutionId);

    /**
     * @param array $params
     * @return null|Db\UserIface
     */
    public function createUser($params = array());

    /**
     * @param $courseId
     * @return null|\Tk\Db\Map\Model|Db\CourseIface
     */
    public function findCourse($courseId);

    /**
     * @param $courseCode
     * @param $institutionId
     * @return null|\Tk\Db\ModelInterface|Db\CourseIface
     */
    public function findCourseByCode($courseCode, $institutionId);

    /**
     * @param $params
     * @return null|Db\CourseIface
     */
    public function createCourse($params);

    /**
     * @param Db\CourseIface $course
     * @param Db\UserIface $user
     */
    public function addUserToCourse($course, $user);

    /**
     * Log in a user object automatically without pass authentication
     *
     * @param Db\UserIface $user
     * @return \Tk\Auth\Result
     */
    public function autoAuthenticate($user);

    /**
     * Return the Uri to redirect to on successful LTI login
     *
     * @param Db\UserIface $user
     * @param Db\CourseIface $course
     * @return \Tk\Uri
     */
    public function getLtiHome($user, $course);

}