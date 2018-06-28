<?php

namespace Uni;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 * @deprecated
 */
abstract class PluginApi
{

    /**
     * @param $username
     * @param $institutionId
     * @return null|\Tk\Db\Map\Model|Db\UserIface
     */
    abstract public function findUser($username, $institutionId);

    /**
     * @param int $subjectId
     * @return null|\Tk\Db\Map\Model|Db\SubjectIface
     */
    abstract public function findSubject($subjectId);

    /**
     * @param string $subjectCode
     * @param int $institutionId
     * @return null|\Tk\Db\ModelInterface|Db\SubjectIface
     */
    abstract public function findSubjectByCode($subjectCode, $institutionId);

    /**
     * @return \Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }




    /**
     * Return the Uri to redirect to on successful LTI login
     *
     * @param Db\UserIface $user
     * @param Db\SubjectIface $subject
     * @return \Tk\Uri
     * @deprecated We need a way to better handle this one, HMMMM!!??
     */
    public function getLtiHome($user, $subject) {}


    /**
     * @param array $params
     * @return null|Db\UserIface
     * @deprecated Use the event
     * @deprecated Use an event (TODO: yet to create one)
     */
    public function createUser($params = array()) {}

    /**
     * @param array $params
     * @return null|Db\SubjectIface
     * @deprecated Use an event (TODO: yet to create one)
     */
    public function createSubject($params) {}

    /**
     * @param Db\SubjectIface $subject
     * @param Db\UserIface $user
     * @deprecated Use an event (TODO: yet to create one)
     */
    public function addUserToSubject($subject, $user) {}

    /**
     * Log in a user object automatically without pass authentication
     *
     * @param Db\UserIface $user
     * @return \Tk\Auth\Result
     * @deprecated Use an event (TODO: yet to create one)
     */
    public function autoAuthenticate($user) {}

}