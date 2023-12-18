<?php
namespace Uni\Controller;

use Tk\Form\Field;
use Tk\Form\Event;
use Bs\Db\User;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Login extends \Bs\Controller\Login
{

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;


    /**
     * Login constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Login');
    }

    /**
     * @param \Tk\Request $request
     * @param string $instHash
     * @throws \Exception
     */
    public function doInsLogin(\Tk\Request $request, $instHash = '')
    {
        $this->getSession()->remove('auth.institutionId');

        $this->institution = $this->getConfig()->getInstitutionMapper()->findByHash($instHash);
        if (!$this->institution && $request->attributes->has('institutionId')) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->attributes->get('institutionId'));
        }
        // get institution by hostname
        if (!$this->institution || !$this->institution->active ) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->findByDomain($request->getTkUri()->getHost());
        }
        // Get the first available institution
        if (!$this->institution || !$this->institution->active ) {
            \Uni\Uri::create('/xlogin.html')->redirect();
            //$this->institution = $this->getConfig()->getInstitutionMapper()->findFiltered([])->current();
        }

        if (!$this->institution || !$this->institution->active ) {
            \Tk\Alert::addWarning('Invalid or inactive Institution. Setup an active institution to continue.');
            \Uni\Uri::create('/index.html')->redirect();
        }
        // no automatic microsoft logins add button
//        else {
//            if (!$this->getAuthUser() && $this->institution->getData()->get('inst.microsoftLogin')) {
//                // Add it ID to the session for the microsoft login to work as expected
//                $this->getSession()->set('auth.institutionId', $this->institution->getId());
//                \Uni\Uri::create('/microsoftLogin.html')->redirect();
//            }
//        }

        if ($this->getAuthUser()) {
            \Bs\Uri::createHomeUrl('/index.html')->redirect();
        }
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->init();

        if ($this->institution) {
            $this->form->appendField(new Field\Hidden('instHash', $this->institution->getHash()));
        }
        $this->form->execute();
    }

    protected function findUser($form)
    {
        if ($this->institution) {
            return $this->getConfig()->getUserMapper()->findByUsername($form->getFieldValue('username'), $this->institution->getId());
        }
    }

    protected function getActivateUrl(User $user)
    {
        if ($this->institution) {
            $url = \Uni\Uri::createInstitutionUrl($this->getConfig()->get('url.auth.activate'), $this->institution);
            return $url->set('h', $user->getHash());
        }
        return parent::getActivateUrl($user);
    }

    /**
     * @throws \Exception
     */
    protected function init()
    {
        if (!$this->form) {
            $this->form = $this->getConfig()->createForm('login-form');
            $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));
            $this->form->addCss('form-horizontal');
        }
        parent::init();
        $this->form->removeField('recoverPassword');
        //$this->form->removeField('register');

        $this->form->appendField(new Event\Submit('login', array($this, 'doLogin')))->removeCss('btn-default')
            ->addCss('btn btn-lg btn-primary btn-ss');

        $recoverUrl = \Uni\Uri::create('/recover.html');
        if ($this->institution) {
            $recoverUrl = \Uni\Uri::createInstitutionUrl('/recover.html', $this->institution);
        }

        $this->form->appendField(new Event\Link('recoverPassword', $recoverUrl, ''))
            ->removeCss('btn btn-sm btn-default btn-once')->addCss('tk-recover-url');
    }

    /**
     * show()
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();
        if ($this->institution) {
            if ($this->institution->getLogoUrl()) {
                $template->setVisible('instLogo');
                $template->setAttr('instLogo', 'src', $this->institution->getLogoUrl()->toString());
            }
            $template->insertText('instName', $this->institution->name);
            $template->setVisible('inst');
            $this->getPage()->getTemplate()->setVisible('hasInst');

            if ($this->institution->getData()->get('inst.microsoftLogin')) {
                $template->setVisible('microsoft');
            }
        } else {
            $template->setVisible('noInst');
            $template->setVisible('recover');
        }

        return $template;
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-login-panel tk-login">

  <div var="form"></div>
  <div class="not-member" choice="register">
    <p>Not a member? <a href="/register.html">Register here</a></p>
  </div>
  <div class="external " choice="inst">
<!--    <a href="/microsoftLogin.html" class="btn btn-lg btn-default col-12" choice="microsoft"><i class="fa fa-windows"></i> Microsoft</a>-->
    <a href="/microsoftLogin.html" class="btn btn-lg btn-default col-12" title="Login using your Microsoft account" choice="microsoft"><img src="/html/app/img/mslogo.png" style="width: 1em;margin-bottom: 4px;"/> Microsoft</a>
<!--    <a href="/googleLogin.html" class="btn btn-lg btn-warning col-12" choice="google">Google</a>-->
<!--    <a href="/githubLogin.html" class="btn btn-lg btn-default col-12" choice="github">Github</a>-->
  </div>

</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
}