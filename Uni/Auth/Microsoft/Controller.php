<?php

namespace Uni\Auth\Microsoft;

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
 *
 *
 */
class Controller extends \Tk\ExtAuth\Microsoft\Controller
{

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;


    /**
     * @param \Tk\Request $request
     * @param string $instHash
     * @throws \Exception
     */
    public function doInsLogin(\Tk\Request $request, $instHash = '')
    {
        $this->institution = $this->getConfig()->getInstitutionMapper()->findByHash($instHash);
        if (!$this->institution && $request->attributes->has('institutionId')) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->attributes->get('institutionId'));
        }

        // get institution by hostname
        if (!$this->institution || !$this->institution->active ) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->findByDomain($request->getTkUri()->getHost());
        }

        if (!$this->institution || !$this->institution->active ) {
            \Tk\Alert::addWarning('Invalid or inactive Institution. Setup an active institution for Microsoft SSO to continue.');
            \Uni\Uri::create('/index.html')->redirect();
        }
        $this->doLogin($request);
    }

    protected function getLoginUrl()
    {
        return Uri::createInstitutionUrl('/microsoftLogin.html', $this->institution);
    }

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
        $idToken = json_decode($token->idToken);
        // Email (use domain portion to ident institution)
        // If not found check the site standard users for a match (exclude admin)
        $username = $idToken->preferred_username;

        // Try to find an existing user
        $user = $this->getConfig()->getUserMapper()->findByEmail($username, $this->institution->getId());
        if (!$user) {
            $user = $this->getConfig()->getUserMapper()->findByUsername($username, $this->institution->getId());
        }
        if (!$user) {
            $this->error = 'No user account found! Please contact your institution`s administrator at: ' . $this->institution->getEmail();
            return;
        }
        $token->userId = $user->getId();
        $token->save();

        $this->getConfig()->getAuth()->getStorage()->write($user->getUsername());
        if ($user && $user->isActive()) {
            $this->getConfig()->setAuthUser($user);
        }
        // Redirect to home page
        $this->getConfig()->getSession()->set('auth.password.access', false);
        \Bs\Uri::createHomeUrl('/index.html', $user)->redirect();
    }

}