<?php
namespace Uni\Listener;

use Tk\Event\GetResponseEvent;
use Uni\Db\Role;
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
        Role::TYPE_ADMIN,           // Highest
        Role::TYPE_CLIENT,
        Role::TYPE_COORDINATOR,
        Role::TYPE_LECTURER,
        Role::TYPE_STAFF,
        Role::TYPE_STUDENT          // Lowest
    );

    /**
     * Add any headers to the final response.
     *
     * @param GetResponseEvent $event
     */
    public function onMasquerade(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->has(static::MSQ)) return;
        try {
            $config = \Uni\Config::getInstance();
            /** @var User $user */
            $user = $config->getUser();
            if (!$user) {
                throw new \Tk\Exception('Unknown User');
            }
            $iid = $config->getInstitutionId();
            if (!$iid)
                $iid = (int)$request->get('institutionId');

            /** @var User $msqUser */
            $msqUser = $config->getUserMapper()->findByhash($request->get(static::MSQ), $iid);
            if (!$msqUser) {
                throw new \Tk\Exception('Invalid User');
            }
            $this->masqueradeLogin($user, $msqUser);
        } catch (\Exception $e) {
            \Tk\Alert::addWarning($e->getMessage());
            \Tk\Uri::create()->remove(static::MSQ)->redirect();
        }
    }



    // -------------------  Masquerade functions  -------------------

    /**
     * Check if this user can masquerade as the supplied msqUser
     *
     * @param User|\Uni\Db\UserIface $user The current User
     * @param User|\Uni\Db\UserIface $msqUser
     * @return bool
     * @throws \Exception
     */
    public function canMasqueradeAs($user, $msqUser)
    {
        $b = parent::canMasqueradeAs($user, $msqUser);
        // If not admins they must be of the same institution
        if ($user->institutionId != 0 && $user->institutionId != $msqUser->institutionId) {
            $b = false;
        }
        return $b;
    }

}