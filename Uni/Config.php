<?php
namespace Uni;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Config extends \Bs\Config
{

    const SID_SUBJECT = 'last.subjectId';


    /**
     * @param string $sitePath
     * @param string $siteUrl
     */
    protected function init($sitePath = '', $siteUrl = '')
    {
        parent::init($sitePath, $siteUrl);
        $this->set('system.lib.uni.path', $this['system.vendor.path'] . '/ttek/tk-uni');
    }


    /**
     * @return string
     */
    public function getLibUniUrl()
    {
        return $this->getSiteUrl() . rtrim($this->get('system.lib.uni.path'), '/');
    }

    /**
     * @return string
     */
    public function getLibUniPath()
    {
        return $this->getSitePath() . rtrim($this->get('system.lib.uni.path'), '/');
    }

    /**
     * Get the Institution object for the logged in user
     *
     * @return Db\InstitutionIface
     * @throws \Tk\Db\Exception
     */
    public function getInstitution()
    {
        if (!$this->get('institution')) {
            $obj = null;
            if ($this->getUser()) {
                $obj = $this->getUser()->getInstitution();
            } else if ($this->getRequest()->has('subjectId')) {
                $subject = $this->findSubject($this->getRequest()->has('subjectId'));
                if ($subject) $obj = $subject->getInstitution();
            }
            $this->set('institution', $obj);
        }
        return $this->get('institution');
    }

    /**
     * @return int|mixed
     * @throws \Tk\Db\Exception
     */
    public function getInstitutionId()
    {
        $institutionId = 0;
        if ($this->getInstitution()) {
            $institutionId = $this->getInstitution()->getId();
        }
        return $institutionId;
    }

    /**
     * If the the current page is a subject page this wi;ll return the subject object
     * based on the subject code in the URI: /staff/VETS50001_2014_SM1/index.html
     *
     * @return \Uni\Db\Subject|null
     * @throws \Tk\Exception
     */
    public function getSubject()
    {
        if (!$this->get('subject') && $this->getUser()) {
            $subject = null;
            if ($this->getInstitution() && $this->getRequest()->getAttribute('subjectCode')) {
                $subjectCode = strip_tags(trim($this->getRequest()->getAttribute('subjectCode')));
                $subject = $this->getInstitution()->findSubjectByCode($subjectCode);
            } else if ($this->getRequest()->has('subjectId')) {
                /** @var Db\SubjectIface $c */
                $c = $this->findSubject($this->getRequest()->get('subjectId'));
                if ($c && $this->getInstitution() && $c->getInstitutionId() == $this->getInstitution()->getId()) {
                    $subject = $c;
                }
            }
            if (!$subject && $this->getSession()->has('lti.subjectId')) { // Check for an LTI default subject selection
                $subject = $this->findSubject(self::getSession()->get('lti.subjectId'));
            }
            if (!$subject && $this->getSession()->has(self::SID_SUBJECT)) {
                $subject = $this->findSubject(self::getSession()->get(self::SID_SUBJECT));
            }
            $this->set('subject', $subject);
            if ($subject) {
                $this->getSession()->set(self::SID_SUBJECT, $subject->getId());
            }
        }

        return $this->get('subject');
    }

    /**
     * @return int|mixed
     * @throws \Tk\Exception
     */
    public function getSubjectId()
    {
        if ($this->getSubject())
            return $this->getSubject()->getId();
        return 0;
    }

    /**
     * unset the subject from the session
     * @throws \Tk\Db\Exception
     */
    public function unsetSubject()
    {
        $this->getSession()->remove(self::SID_SUBJECT);
        $this->remove('subject');
    }

    /**
     * A helper method to create an instance of an Auth adapter
     *
     * @param array $submittedData
     * @return \Tk\Auth\Adapter\Iface
     * @throws \Tk\Db\Exception
     */
    public function getAuthDbTableAdapter($submittedData = array())
    {
        $adapter = new \Uni\Auth\Adapter\DbTable(
            $this->getDb(),
            \Tk\Db\Map\Mapper::$DB_PREFIX . str_replace(\Tk\Db\Map\Mapper::$DB_PREFIX, '', $this['system.auth.dbtable.tableName']),
            $this['system.auth.dbtable.usernameColumn'],
            $this['system.auth.dbtable.passwordColumn'],
            $this['system.auth.dbtable.activeColumn']);
        if (isset($submittedData['instHash'])) {
            $institution = \Uni\Db\InstitutionMap::create()->findByHash($submittedData['instHash']);
            $adapter->setInstitution($institution);
        }
        $adapter->setHashCallback(array(\Tk\Config::getInstance(), 'hashPassword'));
        $adapter->replace($submittedData);
        return $adapter;
    }

    /**
     * @return string
     */
    public function makePageTitle()
    {
        $replace = array('admin-', 'client-', 'staff-', 'student-', '-base');
        /** @var \Tk\Request $request */
        $routeName = $this->getRequest()->getAttribute('_route');
        if ($routeName) {
            $routeName = str_replace($replace, '', $routeName);
            return ucwords(trim(str_replace('-', ' ', $routeName)));
        }
        return '';
    }

    /**
     * @param string $formId
     * @param string $method
     * @param string|null $action
     * @return \Tk\Form
     */
    public static function createForm($formId, $method = \Tk\Form::METHOD_POST, $action = null)
    {
        $form = \Tk\Form::create($formId, $method, $action);
        $form->setDispatcher(self::getInstance()->getEventDispatcher());
        $form->addCss('form-horizontal');
        return $form;
    }

    /**
     * @param $form
     * @return \Tk\Form\Renderer\Dom
     */
    public static function createFormRenderer($form)
    {
        $obj = new \Tk\Form\Renderer\Dom($form);
        $obj->setFieldGroupClass('\Uni\Form\Renderer\HorizontalFieldGroup');
        return $obj;
    }

    /**
     * @param string $title
     * @param string $icon
     * @param bool $withBack
     * @return \Tk\Ui\Admin\ActionPanel
     */
    public static function createActionPanel($title = 'Actions', $icon = 'fa fa-cogs', $withBack = true)
    {
        $ap = \Tk\Ui\Admin\ActionPanel::create($title, $icon);
        if ($withBack) {
            $ap->add(\Tk\Ui\Button::create('Back', 'javascript: window.history.back();', 'fa fa-arrow-left'))
                ->addCss('btn-default btn-once back');
        }
        return $ap;
    }


    // ------------------------------- Commonly Overridden ---------------------------------------


    // TODO: for the find functions maybe we should return the mappers instead, more scalable
    /**
     * @param int $id
     * @return \Uni\Db\InstitutionIface|\Tk\Db\ModelInterface|\Uni\Db\Institution
     * @throws \Tk\Db\Exception
     */
    public function findInstitution($id)
    {
        return \Uni\Db\InstitutionMap::create()->find($id);
    }

    /**
     * @param int $id
     * @return null|\Tk\Db\Map\Model|\Tk\Db\ModelInterface|\Uni\Db\Subject
     * @throws \Tk\Db\Exception
     */
    public function findSubject($id)
    {
        return \Uni\Db\SubjectMap::create()->find($id);
    }

    /**
     * @param int $id
     * @return null|\Tk\Db\Map\Model|\Tk\Db\ModelInterface|\Uni\Db\User
     * @throws \Tk\Db\Exception
     */
    public function findUser($id)
    {
        return \Uni\Db\UserMap::create()->find($id);
    }



    /**
     * Get the current logged in user
     * @return Db\User|Db\UserIface
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * Set the current logged in user
     * @param Db\User|Db\UserIface $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->set('user', $user);
        return $this;
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @param \Uni\Db\UserIface|\Uni\Db\User|null $user
     * @return \Tk\Uri
     */
    public function getUserHomeUrl($user = null)
    {
        if ($user) {
            if ($user->isAdmin())
                return \Tk\Uri::create('/admin/index.html');
            if ($user->isClient())
                return \Tk\Uri::create('/client/index.html');
            if ($user->isStaff())
                return \Tk\Uri::create('/staff/index.html');
            if ($user->isStudent())
                return \Tk\Uri::create('/student/index.html');
        }
        return \Tk\Uri::create('/index.html');   // Should not get here unless their is no roles
    }

    /**
     * getFrontController
     *
     * @return \Bs\FrontController
     * @throws \Tk\Exception
     */
    public function getFrontController()
    {
        if (!$this->get('front.controller')) {
            $obj = new \Uni\FrontController($this->getEventDispatcher(), $this->getResolver());
            $this->set('front.controller', $obj);
        }
        return parent::get('front.controller');
    }

    /**
     * @return \Bs\Listener\AuthHandler
     */
    public function getAuthHandler()
    {
        if (!$this->get('auth.handler')) {
            $this->set('auth.handler', new \Uni\Listener\AuthHandler());
        }
        return $this->get('auth.handler');
    }

    /**
     * @return \Uni\Listener\MasqueradeHandler
     */
    public function getMasqueradeHandler()
    {
        if (!$this->get('auth.masquerade.handler')) {
            $this->set('auth.masquerade.handler', new \Uni\Listener\MasqueradeHandler());
        }
        return $this->get('auth.masquerade.handler');
    }

    /**
     * Create a page for the request
     *
     * @param \Tk\Controller\Iface $controller
     * @return Page
     */
    public function createPage($controller)
    {
        $page = new Page();
        $page->setController($controller);
        if (!$controller->getPageTitle()) {     // Set a default page Title for the crumbs
            $controller->setPageTitle($controller->getDefaultTitle());
        }
        return $page;
    }

    /**
     * @todo This must be implemented in your \App\Config object (for now)
     * @return null|PluginApi
     * @deprecated Find a way to avid using this mess
     */
    public function getPluginApi()
    {
        if (!$this->get('plugin.api')) {
            $this->set('plugin.api', new \Uni\PluginApi());
        }
        return $this->get('plugin.api');
    }

}