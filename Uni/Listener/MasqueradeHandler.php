<?php
namespace Uni\Listener;

use Uni\Db\Permission;
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
     * @deprecated
     */
    public static $roleOrder = array(
        User::TYPE_ADMIN,           // Highest
        User::TYPE_CLIENT,
        User::TYPE_STAFF,
        User::TYPE_STUDENT          // Lowest
    );

    /**
     * Add any headers to the final response.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onMasquerade($event)
    {
        /** @var \Tk\Request $request */
        $request = $event->getRequest();
        if (!$request->has(static::MSQ)) return;
        try {
            $config = \Uni\Config::getInstance();
            /** @var User $user */
            $user = $config->getAuthUser();
            if (!$user) {
                throw new \Tk\Exception('Unknown User');
            }
            $iid = $config->getInstitutionId();
            if (!$iid || $request->has('institutionId'))
                $iid = (int)$request->get('institutionId');

            /** @var User $msqUser */
            $msqUser = $config->getUserMapper()->findByhash($request->get(static::MSQ), $iid);
            if (!$msqUser) {
                throw new \Tk\Exception('Invalid User');
            }
            $this->masqueradeLogin($user, $msqUser);
        } catch (\Exception $e) {
            \Tk\Alert::addWarning($e->getMessage());
            \Tk\Uri::create()->remove(static::MSQ)->remove('institutionId')->redirect();
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
        if (!$user->hasPermission(Permission::CAN_MASQUERADE)) return false;
        $b = parent::canMasqueradeAs($user, $msqUser);
        // If not admins they must be of the same institution
        if ($user->getInstitutionId() != 0 && $user->getInstitutionId() != $msqUser->getInstitutionId()) {
            $b = false;
        }
        return $b;
    }

}