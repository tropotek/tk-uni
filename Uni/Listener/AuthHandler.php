<?php
namespace Uni\Listener;

use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AuthHandler extends \Bs\Listener\AuthHandler
{

    /**
     * @param \Tk\Event\AuthEvent $event
     * @return null|void
     * @throws \Exception
     */
    public function onLoginProcess(\Tk\Event\AuthEvent $event)
    {
        if ($event->getAdapter() instanceof \Tk\Auth\Adapter\Ldap) {
            /** @var \Tk\Auth\Adapter\Ldap $adapter */
            $adapter = $event->getAdapter();
            $config = \Uni\Config::getInstance();

            // Find user data from ldap connection
            $filter = substr($adapter->getBaseDn(), 0, strpos($adapter->getBaseDn(), ','));
            if ($filter) {
                $ldapData = $adapter->ldapSearch($filter);
                if ($ldapData) {
                    $email = trim($ldapData[0]['mail'][0]);   // Email format = firstname.lastname@unimelb
                    $uid = trim($ldapData[0]['auedupersonid'][0]);

                    /* @var \Uni\Db\User $user */
                    $user = $config->getUserMapper()->findByUsername($adapter->get('username'), $config->getInstitutionId());

                    if (!$user) {   // Error out if no user
                        $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
                                $adapter->get('username'), 'Invalid username. Please contact your administrator to setup an account.'));
                        return;
                    }

//                    if (!$user) { // Create a user record if none exists
//
//                        if (!$config->get('auth.ldap.auto.account')) {
//                            $msg = sprintf('Please contact your site administrator to enable your user account. Please provide the following details' .
//                                "\nusername: %s\nUID: %s\nEmail: %s", $adapter->get('username'), $uid, $email);
//                            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, $adapter->get('username'), $msg));
//                        }
//
//                        $type = 'student';
//                        if (preg_match('/(staff|student)/', strtolower($ldapData[0]['auedupersontype'][0]), $reg)) {
//                            if ($reg[1] == 'staff') $type = 'staff';
//                        }
//
//                        if ($type == 'student') {
//                            // To check if a user is pre-enrolled get an array of uid and emails for a user
//                            $isPreEnrolled = $config->getSubjectMapper()->isPreEnrolled($config->getInstitutionId(),
//                                array_merge($ldapData[0]['mail'], $ldapData[0]['mailalternateaddress']),
//                                $ldapData[0]['auedupersonid'][0]
//                            );
//
//                            if (!$isPreEnrolled) {      // Only create users accounts for enrolled students
//                                $msg = sprintf('We cannot find any enrolled subjects. Please contact your coordinator.' .
//                                    "\nusername: %s\nUID: %s\nEmail: %s", $adapter->get('username'), $uid, $email);
//                                $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, $adapter->get('username'), $msg));
//                                return;
//                            }
//
//                            $userData = array(
//                                'authType' => 'ldap',
//                                'institutionId' => $config->getInstitutionId(),
//                                'username' => $adapter->get('username'),
//                                'type' => $type,
//                                'active' => true,
//                                'email' => $email,
//                                'name' => $ldapData[0]['displayname'][0],
//                                'uid' => $ldapData[0]['auedupersonid'][0],
//                                'ldapData' => $ldapData
//                            );
//                            $user = $config->createUser();
//                            $config->getUserMapper()->mapForm($userData, $user);
//                            $error = $user->validate();
//                            if (count($error)) {
//                                try {
//                                    $user->setNewPassword($adapter->get('password'));
//                                } catch (\Exception $e) {
//                                    \Tk\Log::info($e->__toString());
//                                }
//                            } else {
//                                $user->save();
//                                $user->addPermission(\uni\Db\Permission::getPermissionList($user->getType(), false));
//
//                                // Save the last ldap data for reference
//                                $user->getData()->set('ldap.data', json_encode($ldapData, \JSON_PRETTY_PRINT));
//                                $user->getData()->save();
//                            }
//                        } else {
//                            $msg = sprintf('Staff members can contact the site administrator to request access');
//                            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
//                                $adapter->get('username'), $msg));
//                            return;
//                        }
//                    }

                    if ($user && $user->isActive()) {
                        if (!$user->getUid() && !empty($ldapData[0]['auedupersonid'][0]))
                            $user->setUid($ldapData[0]['auedupersonid'][0]);
                        if (!$user->getName() && !empty($ldapData[0]['displayname'][0]))
                            $user->setName($ldapData[0]['displayname'][0]);
                        // TODO: update this to if !$user->email later once all emails are changed over
                        if ($email)
                            $user->setEmail($email);
                        $user->setNewPassword($adapter->get('password'));
                        $user->save();

                        if (method_exists($user, 'getData')) {
                            $data = $user->getData();
                            $data->set('ldap.last.login', json_encode($ldapData));
                            if (!empty($ldapData[0]['ou'][0]))
                                $data->set('faculty', $ldapData[0]['ou'][0]);
                            $data->save();
                        }

                        $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $config->getUserIdentity($user)));
                        $config->getSession()->set('auth.password.access', false);
                    }
                }
            }
        }


        // TODO: Since Canvas we need to re-visit this issue and update to ver 3.0 of the LTI driver
        // TODO: This may need further work, getting a nested session save issue..
        // There is an issue here with going from LDAP and LTI
        //  LTI we only have their name email, however with LDAP the email is their username one GGGRRRR!!
        // EG:
        //  LTI: michael.mifsud@unimelb...
        //  LDAP: mifsudm@unimelb....

        if ($event->getAdapter() instanceof \Lti\Auth\LtiAdapter) {
            /** @var \Lti\Auth\LtiAdapter $adapter */
            $adapter = $event->getAdapter();
            $userData = $adapter->get('userData');
            $config = \Uni\Config::getInstance();
            $ltiData = $adapter->get('ltiData');

            $user = $config->getUserMapper()->findByUsername($adapter->get('username'), $adapter->getInstitution()->getId());
            if (!$user)
                $user = $config->getUserMapper()->findByEmail($userData['email'], $adapter->getInstitution()->getId());

            if (!$user) {   // Error out if no user
                $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
                    $userData['username'], 'Invalid username. Please contact your administrator to setup an account.'));
                return;
            }

//            // TODO: Test that this will work since the role changes 3.2
//            if (!$user) {   // Create the new user account
//                // optional to check the pre-enrollment list before creation
//                $isPreEnrolled = \Uni\Db\Subject::isPreEnrolled($adapter->getInstitution()->getId(), array($userData['email']) );
//                if (!$isPreEnrolled) {  // Only create users accounts for enrolled students
//                    return;
//                }
//                $user = $config->createUser();
//                $user->setType(\Uni\Db\User::TYPE_STUDENT);
//                if ($userData['type'] == 'staff') {
//                    $user->setType(\Uni\Db\User::TYPE_STAFF);
//                    // TODO: setup default permissions for staff
//                }
//                $config->getUserMapper()->mapForm($userData, $user);
//                $user->save();
//                $user->addPermission(\uni\Db\Permission::getPermissionList($user->getType(), false));
//                $adapter->setUser($user);
//            }

            //vd($ltiData);

            $subjectData = $adapter->get('subjectData');
            $subject = $config->getSubjectMapper()->find($subjectData['id']);
            if (!$subject) {
                $subject = $config->getSubjectMapper()->findByCode($subjectData['code'], $adapter->getInstitution()->getId());
            }


            if (!$subject) {
                throw new \Tk\Exception('Subject ['.$subjectData['code'].'] not available, Please contact the subject coordinator.');

                // Create a new subject here if needed
//                $subject = $config->createSubject();
//                $config->getSubjectMapper()->mapForm($subjectData, $subject);
//                $subject->save();
//                $adapter->setSubject($subject);
//                $config->getSubjectMapper()->addUser($subject->getId(), $user->getId());
            } else {
                $event->setRedirect(\Uni\Uri::createSubjectUrl('/index.html', $subject));
            }
            $config->getSession()->set('lti.subjectId', $subject->getId());   // Limit the dashboard to one subject for LTI logins
            $config->getSession()->set('auth.password.access', false);

            // Add user to the subject if not already enrolled as they must be enrolled as LMS says so.... ;-p
            if ($user->isStaff()) {
                $config->getCourseMapper()->addUser($subject->getCourseId(), $user->getId());
            } else if ($user->isStudent()) {
                $config->getSubjectMapper()->addUser($subject->getId(), $user->getId());
            }



            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $config->getUserIdentity($user)));
        }

    }


    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogin(AuthEvent $event)
    {
        $config = \Uni\Config::getInstance();
        $auth = $config->getAuth();

        if ($config->getMasqueradeHandler()->isMasquerading()) {
            $config->getMasqueradeHandler()->masqueradeClear();
        }

        $adapter = $config->getAuthDbTableAdapter($event->all());
        $result = $auth->authenticate($adapter);

        $event->setResult($result);
        $event->set('auth.password.access', true);   // Can modify their own password
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function updateUser(AuthEvent $event)
    {
        $config = \Uni\Config::getInstance();
        parent::updateUser($event);
        if ($config->getMasqueradeHandler()->isMasquerading()) return;
        $user = $config->getAuthUser();
        if ($user) {
            if (property_exists($user, 'sessionId') && $user->getSessionId() != $config->getSession()->getId()) {
                $user->setSessionId($config->getSession()->getId());
            }
            $user->save();

        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        $config = \Uni\Config::getInstance();
        $auth = $config->getAuth();
        /** @var \Uni\Db\User $user */
        $user = $config->getAuthUser();

        if (!$event->getRedirect()) {
            $url = \Tk\Uri::create('/');
            if ($user && !$user->isClient() && !$user->isAdmin() && $user->getInstitution()) {
                $url = \Uni\Uri::createInstitutionUrl('/login.html', $user->getInstitution());
            }
            $event->setRedirect($url);
        }

        if ($user && $user->getId() && property_exists($user, 'sessionId')) {
            $user->setSessionId('');
            $user->save();
        }

        $config->unsetSubject();
        $config->getSession()->remove('lti.subjectId'); // Remove limit the dashboard to one subject for LTI logins
        $config->getSession()->remove('auth.password.access');
        $auth->clearIdentity();

        if (!$config->getMasqueradeHandler()->isMasquerading()) {
            \Tk\Log::warning('Destroying Session');
            $config->getSession()->destroy();
        };
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array_merge(
            array(AuthEvents::LOGIN_PROCESS => 'onLoginProcess'),
            parent::getSubscribedEvents()
        );
    }


}