<?php
namespace Uni;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 * @deprecated
 */
class PluginApi
{

    /**
     * @param $username
     * @param $institutionId
     * @return null|Db\User|\Tk\Db\Map\Model
     * @throws \Tk\Db\Exception
     */
    public function findUser($username, $institutionId)
    {
        \Tk\Log::notice('PluginApi::findUser() called!');
        $user = null;
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = \Uni\Db\UserMap::create()->findByEmail($username, $institutionId);
        } else {
            $user = \Uni\Db\UserMap::create()->findByUsername($username, $institutionId);
        }
        return $user;
    }

    /**
     * @param array $params
     * @return null|Db\User
     * @throws \Exception
     * @deprecated
     */
    public function createUser($params = array())
    {
        \Tk\Log::notice('PluginApi::createUser() called!');
        $user = null;
        switch($params['type']) {
            case 'ldap':
            case 'lti':
                throw new \Exception('Function is no longer available');
//                $user = \App\Config::createNewUser($params['institutionId'],
//                    $params['username'], $params['email'], $params['role'], $params['password'], $params['name'], $params['uid'], $params['active']);
        }

        return $user;
    }

    /**
     * @param $subjectId
     * @return null|\Tk\Db\Map\Model|\app\Db\Subject
     * @throws \Tk\Db\Exception
     */
    public function findSubject($subjectId)
    {
        \Tk\Log::notice('PluginApi::findSubject() called!');
        return \Uni\Db\SubjectMap::create()->find($subjectId);
    }

    /**
     * @param $subjectCode
     * @param $institutionId
     * @return null|Db\Subject|\Tk\Db\ModelInterface
     * @throws \Exception
     */
    public function findSubjectByCode($subjectCode, $institutionId)
    {
        \Tk\Log::notice('PluginApi::findSubjectByCode() called!');
        return \Uni\Db\SubjectMap::create()->findByCode($subjectCode, $institutionId);
    }

    /**
     * @param $params
     * @return Db\Subject|null
     * @throws \Tk\Db\Exception
     */
//    public function createSubject($params)
//    {
//        \Tk\Log::notice('PluginApi::createSubject() called!');
//        $subject = null;
//        switch($params['type']) {
//            case 'lti':
//            case 'ldap':
//                $subject = new \Uni\Db\Subject();
//                try {
//                    \Uni\Db\SubjectMap::create()->mapForm($params, $subject);
//                } catch (\Exception $e) {}
//                $subject->save();
//                $this->addUserToSubject($subject, $params['UserIface']);
//        }
//        return $subject;
//    }

    /**
     * @param \Uni\Db\Subject $subject
     * @param \app\Db\User $user
     */
//    public function addUserToSubject($subject, $user)
//    {
//        \Tk\Log::notice('PluginApi::addUserToSubject() called!');
//        \Uni\Db\SubjectMap::create()->addUser($subject->getId(), $user->getId());
//    }

    /**
     * Log in a user object automatically without pass authentication
     *
     * @param \Uni\Db\User $user
     * @return \Tk\Auth\Result
     * @throws \Tk\Exception
     */
//    public function autoAuthenticate($user)
//    {
//        \Tk\Log::notice('PluginApi::autoAuthenticate() called!');
//        $auth = $this->getConfig()->getAuth();
//        \App\Listener\MasqueradeHandler::masqueradeClear();
//        $authResult = new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $user->getId());
//        $auth->clearIdentity()->getStorage()->write($user->getId());
//        $this->getConfig()->setUser($user);
//        return $authResult;
//    }

    /**
     * Return the Uri to redirect to on successful LTI login
     *
     * @param \Uni\Db\User $user
     * @param \Uni\Db\Subject $subject
     * @return \Tk\Uri
     * @throws \Exception
     * @deprecated
     */
//    public function getLtiHome($user, $subject)
//    {
//        \Tk\Log::notice('PluginApi::getLtiHome() called!');
//        return $user->getHomeUrl();
//    }


    /**
     * @return \Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }
}