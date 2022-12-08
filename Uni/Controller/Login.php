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
            $this->institution = $this->getConfig()->getInstitutionMapper()->findFiltered(array())->current();
        }

        if (!$this->institution || !$this->institution->active ) {
            \Tk\Alert::addWarning('Invalid or inactive Institution. Setup an active institution to continue.');
            \Uni\Uri::create('/index.html')->redirect();
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
        //$this->form->removeField('forgotPassword');
        //$this->form->removeField('register');

        $this->form->appendField(new Event\Submit('login', array($this, 'doLogin')))->removeCss('btn-default')
            ->addCss('btn btn-lg btn-primary btn-ss');

        if (!$this->institution) {
            $this->form->appendField(new Event\Link('forgotPassword', \Tk\Uri::create('/recover.html'), ''))
                ->removeCss('btn btn-sm btn-default btn-once')->addCss('tk-recover-url');
        }
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
  <div class="external row" choice="inst">
    <a href="/microsoftLogin.html" class="btn btn-lg btn-default col-12" choice="microsoft">Microsoft</a>
<!--    <a href="/googleLogin.html" class="btn btn-lg btn-warning col-12" choice="google">Google</a>-->
<!--    <a href="/githubLogin.html" class="btn btn-lg btn-default col-12" choice="github">Github</a>-->
  </div>

</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
}