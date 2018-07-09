<?php
namespace Uni\Listener;

use Tk\Kernel\KernelEvents;
use Tk\Event\GetResponseEvent;
use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;
use Uni\Db\User;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MasqueradeHandler extends \Bs\Listener\MasqueradeHandler
{

    /**
     * The order of role permissions
     * @var array
     */
    public static $roleOrder = array(
        User::ROLE_ADMIN,           // Highest
        User::ROLE_CLIENT,
        User::ROLE_STAFF,
        User::ROLE_STUDENT          // Lowest
    );

    /**
     * Add any headers to the final response.
     *
     * @param GetResponseEvent $event
     */
    public function onMasquerade(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->has(self::MSQ)) return;
        try {
            $config = \Uni\Config::getInstance();
            /** @var User $user */
            $user = $config->getUser();
            if (!$user) throw new \Tk\Exception('Unknown User');
            $iid = $config->getInstitutionId();
            if (!$iid)
                $iid = (int)$request->get('institutionId');
            /** @var User $msqUser */
            $msqUser = \Uni\Db\UserMap::create()->findByhash($request->get(self::MSQ), $iid);

            if (!$msqUser) throw new \Tk\Exception('Invalid User');
            self::masqueradeLogin($user, $msqUser);
        } catch (\Exception $e) {
            \Tk\Alert::addWarning($e->getMessage());
            \Tk\Uri::create()->remove(self::MSQ)->redirect();
        }
    }



    // -------------------  Masquerade functions  -------------------

    /**
     * Check if this user can masquerade as the supplied msqUser
     *
     * @param User|\Uni\Db\UserIface $user
     * @param User|\Uni\Db\UserIface $msqUser
     * @return bool
     */
    public static function canMasqueradeAs($user, $msqUser)
    {
        if (!$msqUser || !$user) return false;
        if ($user->id == $msqUser->id) return false;

        $msqArr = \Uni\Config::getInstance()->getSession()->get(self::SID);
        if (is_array($msqArr)) {    // Check if we are allready masquerading as this user in the queue
            foreach ($msqArr as $data) {
                if ($data['userId'] == $msqUser->id) return false;
            }
        }

        // Get the users role precedence order index
        $userRoleIdx = array_search($user->role, self::$roleOrder);
        $msqRoleIdx = array_search($msqUser->role, self::$roleOrder);

        // If not admin their role must be higher in precedence see \Uni\Db\User::$roleOrder
        if (!$user->hasRole(\Uni\Db\User::ROLE_ADMIN) && $userRoleIdx >= $msqRoleIdx) {
            return false;
        }

        // If not admins they must be of the same institution
        if (!$user->hasRole(\Uni\Db\User::ROLE_ADMIN) && $user->getInstitution()->id != $msqUser->institutionId) {
            return false;
        }
        return true;
    }

    /**
     * If this user is masquerading
     *
     * 0 if not masquerading
     * >0 The masquerading total (for nested masquerading)
     *
     * @return int
     * @throws \Tk\Exception
     * @throws \Tk\Exception
     */
    public static function isMasquerading()
    {
        if (!\Uni\Config::getInstance()->getSession()->has(self::SID)) return 0;
        $msqArr = \Uni\Config::getInstance()->getSession()->get(self::SID);
        return count($msqArr);
    }

    /**
     * Get the user who is masquerading, ignoring any nested masqueraded users
     *
     * @return \Uni\Db\User|null
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Exception
     */
    public static function getMasqueradingUser()
    {
        $user = null;
        if (\Uni\Config::getInstance()->getSession()->has(self::SID)) {
            $msqArr = current(\Uni\Config::getInstance()->getSession()->get(self::SID));
            /** @var \Uni\Db\User $user */
            $user = \Uni\Db\UserMap::create()->find($msqArr['userId']);
        }
        return $user;
    }

    /**
     *
     * @param User $user
     * @param User $msqUser
     * @return bool|void
     * @throws \Tk\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Exception
     */
    public static function masqueradeLogin($user, $msqUser)
    {
        if (!$msqUser || !$user) return;
        if ($user->id == $msqUser->id) return;

        // Get the masquerade queue from the session
        $msqArr = \Uni\Config::getInstance()->getSession()->get(self::SID);
        if (!is_array($msqArr)) $msqArr = array();

        if (!self::canMasqueradeAs($user, $msqUser)) {
            return;
        }

        // Save the current user and url to the session, to allow logout
        $userData = array(
            'userId' => $user->id,
            'url' => \Tk\Uri::create()->remove(self::MSQ)->toString()
        );
        array_push($msqArr, $userData);
        // Save the updated masquerade queue
        \Uni\Config::getInstance()->getSession()->set(self::SID, $msqArr);

        // Login as the selected user
        \Uni\Config::getInstance()->getAuth()->getStorage()->write($msqUser->id);
        \Tk\Uri::create($msqUser->getHomeUrl())->redirect();
    }

    /**
     * masqueradeLogout
     *
     * @throws \Tk\Exception
     */
    public static function masqueradeLogout()
    {
        if (!self::isMasquerading()) return;
        if (!\Uni\Config::getInstance()->getAuth()->hasIdentity()) return;
        $msqArr = \Uni\Config::getInstance()->getSession()->get(self::SID);
        if (!is_array($msqArr) || !count($msqArr)) return;

        $userData = array_pop($msqArr);
        if (empty($userData['userId']) || empty($userData['url']))
            throw new \Tk\Exception('Session data corrupt. Clear session data and try again.');
        
        $userId = (int)$userData['userId'];
        $url = \Tk\Uri::create($userData['url']);

        // Save the updated masquerade queue
        \Uni\Config::getInstance()->getSession()->set(self::SID, $msqArr);

        \Uni\Config::getInstance()->getAuth()->getStorage()->write($userId);
        $url->redirect();
    }

    /**
     * masqueradeLogout
     *
     * @throws \Tk\Exception
     */
    public static function masqueradeClear()
    {
        \Uni\Config::getInstance()->getSession()->remove(self::SID);
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        if (self::isMasquerading()) {   // stop masquerading
            self::masqueradeLogout();
        }
    }

    /**
     * getSubscribedEvents
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onMasquerade',
            AuthEvents::LOGOUT => array('onLogout', 10)
        );
    }
}