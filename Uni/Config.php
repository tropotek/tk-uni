<?php
namespace Uni;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class Config extends \Bs\Config
{

    const SID_SUBJECT = 'last.subjectId';


    /**
     *
     */
    protected function init()
    {
        parent::init();
        $this->set('system.lib.uni.path', $this['system.vendor.path'] . '/ttek/tk-uni');
    }

    /**
     * Load the site route config files
     */
    public function loadConfig()
    {
        include($this->getLibBasePath() . '/config/application.php');
        include($this->getLibUniPath() . '/config/application.php');
        $this->loadAppConfig();
    }

    /**
     * Load the site route config files
     */
    public function loadRoutes()
    {
        include($this->getLibBasePath() . '/config/routes.php');
        include($this->getLibUniPath() . '/config/routes.php');
        $this->loadAppRoutes();
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
     * @return null|Db\Institution|Db\InstitutionIface
     * @throws \Exception
     */
    public function getInstitution()
    {
        // TODO: this needs to be refactored and made simpler and as getSubject()
        if (!$this->get('institution')) {
            $obj = null;
            if ($this->getUser()) {
                $obj = $this->getUser()->getInstitution();          // TODO: This should be all we need???
                                                                    // TODO: In cases of public pages there should be an institution url that
                                                                    // TODO: uses the domain or the has path of that institution....
                                                                    // OH! What about Client with institution_id = 0
            } else if ($this->getRequest()->has('subjectId')) {
                \TK\Log::warning('This code should not be reached ever???');
                /** @var Db\Subject $subject */
                try {
                    $subject = $this->getSubjectMapper()->find($this->getRequest()->has('subjectId'));
                    if ($subject) $obj = $subject->getInstitution();
                } catch (\Exception $e) {
                    \Tk\Log::error($e->__toString());
                }
            }
            $this->set('institution', $obj);
        }
        return $this->get('institution');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getInstitutionId()
    {
        $institutionId = 0;
        if ($this->getInstitution()) {
            $institutionId = $this->getInstitution()->getId();
        }
        return (int)$institutionId;
    }

    /**
     * If the the current page is a subject page this wi;ll return the subject object
     * based on the subject code in the URI: /staff/VETS50001_2014_SM1/index.html
     *
     * @return null|Db\Subject|Db\SubjectIface
     */
    public function getSubject()
    {
        // TODO: this needs to be refactored and made simpler same as getInstitution()
        if (!$this->get('subject') && $this->getUser()) {
            try {
                $subject = null;
                if ($this->getInstitution() && $this->getRequest()->getAttribute('subjectCode')) {
                    $subjectCode = strip_tags(trim($this->getRequest()->getAttribute('subjectCode')));
                    $subject = $this->getInstitution()->findSubjectByCode($subjectCode);
                } else if ($this->getRequest()->has('subjectId')) {
                    /** @var Db\Subject $c */
                    $c = $this->getSubjectMapper()->find($this->getRequest()->get('subjectId'));
                    if ($c && $this->getInstitution() && $c->getInstitutionId() == $this->getInstitution()->getId()) {
                        $subject = $c;
                    }
                }
                if (!$subject && $this->getSession()->has('lti.subjectId')) { // Check for an LTI default subject selection
                    $subject = $this->getSubjectMapper()->find(self::getSession()->get('lti.subjectId'));
                }
                if (!$subject && $this->getSession()->has(self::SID_SUBJECT)) {
                    $subject = $this->getSubjectMapper()->find(self::getSession()->get(self::SID_SUBJECT));
                }
                $this->set('subject', $subject);
                if ($subject) {
                    $this->getSession()->set(self::SID_SUBJECT, $subject->getId());
                }
            } catch (\Exception $e) {
                \Tk\Log::error($e->__toString());
            }
        }

        return $this->get('subject');
    }

    /**
     * @return int
     */
    public function getSubjectId()
    {
        $sid = 0;
        if ($this->getSubject())
            $sid = $this->getSubject()->getId();
        return (int)$sid;
    }

    /**
     * unset the subject from the session
     */
    public function unsetSubject()
    {
        $this->getSession()->remove(self::SID_SUBJECT);
        $this->remove('subject');
    }

    /**
     * @param bool $skip
     * @return null|\Tk\Db\Map\Model|Db\SubjectIface
     * @throws \Exception
     */
    public function getLastCreatedSubject($skip = false)
    {
        $list = $this->getSubjectMapper()->findFiltered(array('institutionId' => $this->getInstitutionId()),
            \Tk\Db\Tool::create('created DESC'), 2);
        $subject = $list->get(0);
        if ($skip) $subject = $list->get(1);
        return $subject;
    }


    /**
     * @param null|\Uni\Db\Subject $subject
     * @return $this
     */
    public function resetCrumbs($subject = null)
    {
        if ($this->getCrumbs()) {
            $this->getCrumbs()->reset();
            if ($subject) {
                $this->getCrumbs()->addCrumb($subject->getName(), \Uni\Uri::createSubjectUrl('/index.html', $subject));
            }
        }
        return $this;
    }

    /**
     * This function returns true if the url is one that uses the {subjectCode}
     *
     * @return bool
     */
    public function isSubjectUrl()
    {
        if (!$this->get('is.subject.url')) {
            $b = false;
            /** @var \Tk\Routing\Route $route */
            $route = $this->getRouteCollection()->get($this->getRequest()->getAttribute('_route'));
            if ($route) {
                $vars = $route->compile()->getPathVariables();
                $b = in_array('subjectCode', $vars);
            }
            $this->set('is.subject.url', $b);
        }
        return $this->get('is.subject.url');
    }

    /**
     * A helper method to create an instance of an Auth adapter
     *
     * @param array $submittedData
     * @return \Tk\Auth\Adapter\Iface
     * @throws \Exception
     */
    public function getAuthDbTableAdapter($submittedData = array())
    {
        $adapter = new \Uni\Auth\Adapter\DbTable(
            $this->getDb(),
            \Tk\Db\Map\Mapper::$DB_PREFIX . str_replace(\Tk\Db\Map\Mapper::$DB_PREFIX, '', $this['system.auth.dbtable.tableName']),
            $this['system.auth.dbtable.usernameColumn'],
            $this['system.auth.dbtable.passwordColumn']);

        if (isset($submittedData['instHash'])) {
            $institution = $this->getInstitutionMapper()->findByHash($submittedData['instHash']);
            $adapter->setInstitution($institution);
        }
        $adapter->setHashCallback(array($this, 'hashPassword'));
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
     * @return \Tk\Form
     */
    public function createForm($formId)
    {
        $form = \Tk\Form::create($formId);
        $form->setDispatcher($this->getEventDispatcher());
        return $form;
    }

    /**
     * @param $form
     * @return \Tk\Form\Renderer\Dom
     */
    public function createFormRenderer($form)
    {
        $obj = \Tk\Form\Renderer\Dom::create($form);
        $obj->setFieldGroupRenderer($this->getFormFieldGroupRenderer($form));
        $obj->getLayout()->setDefaultCol('col');
        return $obj;
    }



    // ------------------------------- Commonly Overridden ---------------------------------------


    /**
     * @param int $id
     * @return \Uni\Db\InstitutionIface|\Tk\Db\ModelInterface|\Uni\Db\Institution
     * @throws \Exception
     * @deprecated Use the getInstitutionMapper() method
     */
    public function findInstitution($id)
    {
        return $this->getInstitutionMapper()->find($id);
    }

    /**
     * @param int $id
     * @return null|\Tk\Db\Map\Model|\Tk\Db\ModelInterface|\Uni\Db\Subject
     * @throws \Exception
     * @deprecated Use the getSubjectMapper() method
     */
    public function findSubject($id)
    {
        return $this->getSubjectMapper()->find($id);
    }

    /**
     * @param int $id
     * @return null|\Tk\Db\Map\Model|\Tk\Db\ModelInterface|Db\User|Db\UserIface
     * @throws \Exception
     * @deprecated Use the getUserMapper() method
     */
    public function findUser($id)
    {
        return $this->getUserMapper()->find($id);
    }

    /**
     * @param Db\User $user
     * @return int|string
     */
    public function getUserIdentity($user)
    {
        return $user->getId();
    }



    /**
     * @return Db\RoleMap
     */
    public function getRoleMapper()
    {
        if (!$this->get('obj.mapper.role')) {
            $this->set('obj.mapper.role', Db\RoleMap::create());
        }
        return $this->get('obj.mapper.role');
    }

    /**
     * @return Db\Role
     */
    public function createRole()
    {
        return new Db\Role();
    }

    /**
     * @return Db\InstitutionMap
     */
    public function getInstitutionMapper()
    {
        if (!$this->get('obj.mapper.institution')) {
            $this->set('obj.mapper.institution', Db\InstitutionMap::create());
        }
        return $this->get('obj.mapper.institution');
    }

    /**
     * @return Db\Institution|Db\InstitutionIface
     */
    public function createInstitution()
    {
        return new Db\Institution();
    }

    /**
     * @return Db\SubjectMap
     */
    public function getSubjectMapper()
    {
        if (!$this->get('obj.mapper.subject')) {
            $this->set('obj.mapper.subject', Db\SubjectMap::create());
        }
        return $this->get('obj.mapper.subject');
    }

    /**
     * @return Db\Subject|Db\SubjectIface
     */
    public function createSubject()
    {
        return new Db\Subject();
    }

    /**
     * @return Db\UserMap
     */
    public function getUserMapper()
    {
        if (!$this->get('obj.mapper.user')) {
            $this->set('obj.mapper.user', Db\UserMap::create());
        }
        return $this->get('obj.mapper.user');
    }

    /**
     * @return Db\User|Db\UserIface
     */
    public function createUser()
    {
        return new Db\User();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function canChangePassword()
    {
        return (!$this->getSession()->has('auth.password.access') || $this->getSession()->get('auth.password.access'));
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @param \Uni\Db\UserIface|\Uni\Db\User|null $user
     * @return \Tk\Uri
     */
    public function getUserHomeUrl($user = null)
    {
        if (!$user) $user = $this->getUser();
        $url = \Tk\Uri::create('/');
        if ($user) {
            if ($user->isStudent())
                $url = \Tk\Uri::create('/student/index.html');
            if ($user->isStaff())
                $url = \Tk\Uri::create('/staff/index.html');
            if ($user->isClient())
                $url = \Tk\Uri::create('/client/index.html');
            if ($user->isAdmin())
                $url = \Tk\Uri::create('/admin/index.html');
        }
        return $url;
    }

    /**
     * @return array
     */
//    public function getAvailableUserRoleTypes()
//    {
//        vd();
//        return \Tk\ObjectUtil::getClassConstants('Uni\Db\Role', 'TYPE');
//    }

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
     * @param \Tk\Event\Dispatcher $dispatcher
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function setupDispatcher($dispatcher)
    {
        \Uni\Dispatch::create($dispatcher);
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
     * @return \Bs\Listener\PageTemplateHandler
     */
    public function getPageTemplateHandler()
    {
        if (!$this->get('page.template.handler')) {
            $this->set('page.template.handler', new \Uni\Listener\PageTemplateHandler());
        }
        return $this->get('page.template.handler');
    }

    /**
     * @param string $templatePath (optional)
     * @return Page
     */
    public function createPage($templatePath = '')
    {
        try {
            return new Page($templatePath);
        } catch (\Exception $e) {
            \Tk\Log::error($e->__toString());
        }
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