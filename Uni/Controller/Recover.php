<?php
namespace Uni\Controller;

use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Request;
use Tk\Auth\AuthEvents;
use Bs\Controller\Iface;
use Uni\Uri;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Recover extends Iface
{

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;

    /**
     * @var \Tk\Form
     */
    protected $form = null;



    /**
     * Login constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Recover Password');
    }

    /**
     * @return \Tk\Controller\Page
     */
    public function getPage()
    {
        if (!$this->page) {
            $templatePath = '';
            if ($this->getConfig()->get('template.login')) {
                $templatePath = $this->getConfig()->getSitePath() . $this->getConfig()->get('template.login');
            }
            $this->page = $this->getConfig()->createPage($templatePath);
        }
        return parent::getPage();
    }

    public function doInsRecover(\Tk\Request $request, $instHash = '')
    {
        $this->institution = $this->getConfig()->getInstitutionMapper()->findByHash($instHash);
        if (!$this->institution && $request->attributes->has('institutionId')) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->attributes->get('institutionId'));
        }
        // get institution by hostname
        if (!$this->institution || !$this->institution->active ) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->findByDomain($request->getTkUri()->getHost());
        }

        $this->doDefault($request);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->init();

        $this->form->execute();
    }

    /**
     *
     */
    protected function init()
    {
        if (!$this->form) {
            $this->form = $this->getConfig()->createForm('recover-account');
            $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));
            $this->form->addCss('form-horizontal');
        }

        $this->form->appendField(new Field\InputGroup('account'))->setAttr('placeholder', 'Username');

        $this->form->appendField(new Event\Submit('recover', array($this, 'doRecover')))->removeCss('btn-default')->addCss('btn btn-primary btn-ss');

        $loginUrl = \Tk\Uri::create('/xlogin.html');
        if ($this->institution) {
            $loginUrl = \Uni\Uri::createInstitutionUrl('/login.html');
        }

        $this->form->appendField(new Event\Link('login', $loginUrl, ''))
            ->removeCss('btn btn-sm btn-default btn-once')->addCss('tk-login-url');

    }


    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doRecover($form, $event)
    {
        if (!$form->getFieldValue('account')) {
            $form->addFieldError('account', 'Please enter your username or email');
        }

        if ($form->hasErrors()) {
            return;
        }

        $account = $form->getFieldValue('account');
        /** @var \Uni\Db\User $user */
        $user = null;
        $iid = 0;
        if ($this->institution) {
            $iid = $this->institution->getId();
        }
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getConfig()->getUserMapper()->findByEmail($account, $iid);
        } else {
            $user = $this->getConfig()->getUserMapper()->findByUsername($account, $iid);
        }

        if (!$user || !$user->isActive() || $user->getId() == 1) {
            $form->addFieldError('account', 'Please enter a valid username or email');
            return;
        }

        // Fire the login event to allow developing of misc auth plugins
        $e = new \Tk\Event\Event();
        $e->set('form', $form);
        $e->set('user', $user);

        if ($this->getConfig()->getInstitution()) {
            $activateUrl = Uri::createInstitutionUrl(\Bs\Config::getInstance()->get('url.auth.activate'));
            $e->set('activateUrl', $activateUrl);
        }

        $this->getConfig()->getEventDispatcher()->dispatch($e, AuthEvents::RECOVER);

        \Tk\Alert::addSuccess('You new access details have been sent to your email address.');
        $event->setRedirect(\Tk\Uri::create());
        
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('form', $this->form->getRenderer()->show());

        if ($this->getConfig()->get('site.client.registration')) {
            $template->setVisible('register');
        }

        return $template;
    }


    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-login-panel tk-recover">

  <div var="form"></div>
  <div class="not-member" choice="register">
    <p>Not a member? <a href="/register.html">Register here</a></p>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}