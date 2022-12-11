<?php

namespace Uni\Auth\Microsoft;

use Tk\Alert;
use Tk\Exception;
use Uni\Db\Institution;
use Uni\Db\User;
use Tk\ExtAuth\Microsoft\Token;
use Uni\Uri;


/**
 * Azure: https://portal.azure.com/
 * You must setup the app in Azure te get these values. This will not be covered here.
 *
 * Add the following to the config.php
 * ```
 *   // Microsoft SSO settings
 *   $config['auth.microsoft.enabled'] = true;
 *   // tennant ID or `common` for multi-tenant
 *   $config['auth.microsoft.tenantid'] = 'common';
 *   $config['auth.microsoft.clientid'] = '';
 *   $config['auth.microsoft.logout'] = 'https://login.microsoftonline.com/common/wsfederation?wa=wsignout1.0';
 *   $config['auth.microsoft.scope'] = 'openid offline_access profile user.read';
 *   // method = 'certificate' or 'secret'
 *   $config['auth.microsoft.oauth.method'] = 'secret';
 *   $config['auth.microsoft.oauth.secret'] = '';
 *   // on Windows, the certificate paths should be in the form c:/path/to/cert.crt
 *   //$config['auth.microsoft.oauth.certfile'] = '/data/cert/certificate.crt';
 *   //$config['auth.microsoft.oauth.keyfile'] = '/data/cert/privatekey.pem';
 * ```
 * Add the following routes:
 * ```
 *    $routes->add('login-microsoft', Route::create('/microsoftLogin.html', 'Tk\ExtAuth\Microsoft\Controller::doLogin'));
 *    $routes->add('auth-microsoft', Route::create('/microsoftAuth.html',  'Tk\ExtAuth\Microsoft\Controller::doAuth'));
 * ```
 * Add the following to the login page:
 * ```
 *    <a href="/microsoftLogin.html" class="btn btn-lg btn-default col-12" choice="microsoft">Microsoft</a>
 * ```
 *
 */
class Controller extends \Tk\ExtAuth\Microsoft\Controller
{

    /**
     * Find/Create user once the token is validated.
     *
     * redirect to user homepage or set an error if not found
     *
     * @param Token $token
     * @return void
     * @throws \Exception
     */
    protected function findUser($token)
    {
        /** @var Institution $institution */
        $institution = $this->getConfig()->getInstitutionMapper()->find($this->getSession()->get('auth.institutionId'));

        if (!$institution) {
            $this->error = 'Cannot find institution`s login page. Please <a href="'
                . htmlentities($this->getConfig()->get('auth.microsoft.logout'))
                . '">logout</a> and try again';
            return;
            //Alert::addWarning('Cannot find your institution`s login page. Please Try again.');
            //\Tk\Uri::create('/index.html')->redirect();
        }
        if (!$institution->getData()->get('inst.microsoftLogin')) {
            $this->error = 'Microsoft login not enabled on this account, please contact your administrator: ' . $institution->getEmail();
            return;
        }

        $idToken = json_decode($token->idToken);
        // Email (use domain portion to ident institution)
        // If not found check the site standard users for a match (exclude admin)
        $username = $idToken->preferred_username;

        // Try to find an existing user
        $user = $this->getConfig()->getUserMapper()->findByEmail($username, $institution->getId());
        if (!$user) {
            $user = $this->getConfig()->getUserMapper()->findByUsername($username, $institution->getId());
        }
        if (!$user) {
            $this->error = 'No user account found! Your institution administrator has been notified and should contact you soon: ' . $institution->getEmail();
            $this->emailAdmin($institution, $idToken);
            return;
        }
        $token->userId = $user->getId();
        $token->save();

        $this->getConfig()->getAuth()->getStorage()->write($this->getConfig()->getUserIdentity($user));
        if ($user->isActive()) {
            $this->getConfig()->setAuthUser($user);
        }
        // Redirect to home page
        $this->getSession()->remove('auth.institutionId');
        $this->getConfig()->getSession()->set('auth.password.access', false);
        \Bs\Uri::createHomeUrl('/index.html', $user)->redirect();
    }

    /**
     * @param Institution $institution
     * @param \stdClass $idToken
     * @return void
     */
    public function emailAdmin($institution, $idToken)
    {
        $message = $this->getConfig()->createMessage();
        $content = <<<HTML
    <h2>New User Request For {institutionName}.</h2>
    <p>
      A user from your institution has attempted to login using the Microsoft SSO login page.
      However the user does not have an account or the account email/username is not correct.
    </p>
    <p>
      If you wish to grant this user access to the APD, <a href="{url}">login to the APD</a> and create 
      or update the user account with the following details.
    </p>
    <p>
       <b>Name:</b> {name}<br/>
       <b>Username:</b> {username}<br/>
       <b>Email:</b> {email}<br/>
       <b>Institution:</b> {institutionName}
    </p>

    <p>
      Once completed contact the user at {email} and ask them to signin.
    </p>

HTML;
        $message->set('content', $content);
        $message->setSubject(' New User Request For ' . $institution->getName());
        $message->addTo($institution->getEmail());
        $message->set('institutionName', $institution->getName());
        $message->set('name', $idToken->name);
        $message->set('username', $idToken->preferred_username);
        $message->set('email', $idToken->preferred_username);
        $message->set('url', $institution->getLoginUrl()->toString());

        \Bs\Config::getInstance()->getEmailGateway()->send($message);
    }


}