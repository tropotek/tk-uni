<?php
namespace Uni;

use Tk\Db\Pdo;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
abstract class Config extends \Tk\Config
{

    const SID_SUBJECT = 'last.subjectId';


    /**
     * getRequest
     *
     * @return \Tk\Request
     */
    public function getRequest()
    {
        if (!parent::getRequest()) {
            $obj = \Tk\Request::create();
            //$obj->setAttribute('config', $this);
            parent::setRequest($obj);
        }
        return parent::getRequest();
    }

    /**
     * getCookie
     *
     * @return \Tk\Cookie
     */
    public function getCookie()
    {
        if (!parent::getCookie()) {
            $obj = new \Tk\Cookie($this->getSiteUrl());
            parent::setCookie($obj);
        }
        return parent::getCookie();
    }

    /**
     * getSession
     *
     * @return \Tk\Session
     * @throws \Tk\Db\Exception
     */
    public function getSession()
    {
        if (!parent::getSession()) {
            $adapter = $this->getSessionAdapter();
            $obj = \Tk\Session::getInstance($adapter, $this, $this->getRequest(), $this->getCookie());
            parent::setSession($obj);
        }
        return parent::getSession();
    }

    /**
     * getSessionAdapter
     *
     * @return \Tk\Session\Adapter\Iface|null
     * @throws \Tk\Db\Exception
     */
    public function getSessionAdapter()
    {
        if (!$this->get('session.adapter')) {
            //$adapter = null;
            $adapter = new \Tk\Session\Adapter\Database($this->getDb(), new \Tk\Encrypt());
            $this->set('session.adapter', $adapter);
        }
        return $this->get('session.adapter');
    }

    /**
     * Ways to get the db after calling this method
     *
     *  - \Uni\Config::getInstance()->getDb()       //
     *  - \Tk\Db\Pdo::getInstance()                //
     *
     * Note: If you are creating a base lib then the DB really should be sent in via a param or method.
     *
     * @param string $name
     * @return mixed|Pdo
     */
    public function getDb($name = 'db')
    {
        if (!$this->get('db') && $this->has($name.'.type')) {
            try {
                $pdo = Pdo::getInstance($name, $this->getGroup($name, true));
                $this->set('db', $pdo);
            } catch (\Exception $e) {
                error_log('<p>Config::getDb(): ' . $e->getMessage() . '</p>');
                exit;
            }
        }
        return $this->get('db');
    }

    /**
     * getEventDispatcher
     *
     * @return \Tk\Event\Dispatcher
     */
    public function getEventDispatcher()
    {
        if (!$this->get('event.dispatcher')) {
            $log = null;
            if ($this->get('event.dispatcher.log')) {
                $log = $this->getLog();
            }
            $obj = new \Tk\Event\Dispatcher($log);
            $this->set('event.dispatcher', $obj);
        }
        return $this->get('event.dispatcher');
    }

    /**
     * getResolver
     *
     * @return \Tk\Controller\Resolver
     */
    public function getResolver()
    {
        if (!$this->get('resolver')) {
            $obj = new \Uni\Controller\PageResolver($this->getLog());
            $this->set('resolver', $obj);
        }
        return $this->get('resolver');
    }

    /**
     * get a dom Modifier object
     *
     * @return \Dom\Modifier\Modifier
     * @throws \Tk\Exception
     */
    public function getDomModifier()
    {
        if (!$this->get('dom.modifier')) {
            $dm = new \Dom\Modifier\Modifier();
            $dm->add(new \Dom\Modifier\Filter\UrlPath($this->getSiteUrl()));
            $dm->add(new \Dom\Modifier\Filter\JsLast());
            if (class_exists('Dom\Modifier\Filter\Less')) {
                $less = $dm->add(new \Dom\Modifier\Filter\Less($this->getSitePath(), $this->getSiteUrl(), $this->getCachePath(),
                    array('siteUrl' => $this->getSiteUrl(), 'dataUrl' => $this->getDataUrl(), 'templateUrl' => $this->getTemplateUrl())));
                $less->setCompress(true);
            }
            if ($this->isDebug()) {
                $dm->add($this->getDomFilterPageBytes());
            }
            $this->set('dom.modifier', $dm);
        }
        return $this->get('dom.modifier');
    }

    /**
     * @return \Dom\Modifier\Filter\PageBytes
     */
    public function getDomFilterPageBytes()
    {
        if (!$this->get('dom.filter.page.bytes')) {
            $obj = new \Dom\Modifier\Filter\PageBytes($this->getSitePath());
            $this->set('dom.filter.page.bytes', $obj);
        }
        return $this->get('dom.filter.page.bytes');
    }

    /**
     * getDomLoader
     *
     * @return \Dom\Loader
     */
    public function getDomLoader()
    {
        if (!$this->get('dom.loader')) {
            $dl = \Dom\Loader::getInstance()->setParams($this->all());
            $dl->addAdapter(new \Dom\Loader\Adapter\DefaultLoader());
            /** @var \Uni\Controller\Iface $controller */
            $controller = $this->getRequest()->getAttribute('controller.object');
            if ($controller->getPage()) {
                $templatePath = dirname($controller->getPage()->getTemplatePath());
                $xtplPath = str_replace('{templatePath}', $templatePath, $this['template.xtpl.path']);
                $dl->addAdapter(new \Dom\Loader\Adapter\ClassPath($xtplPath, $this['template.xtpl.ext']));
            }
            $this->set('dom.loader', $dl);
        }
        return $this->get('dom.loader');
    }

    /**
     * getAuth
     *
     * @return \Tk\Auth
     * @throws \Tk\Exception
     */
    public function getAuth()
    {
        if (!$this->get('auth')) {
            $obj = new \Tk\Auth(new \Tk\Auth\Storage\SessionStorage($this->getSession()));
            $this->set('auth', $obj);
        }
        return $this->get('auth');
    }

    /**
     * getEmailGateway
     *
     * @return \Tk\Mail\Gateway
     */
    public function getEmailGateway()
    {
        if (!$this->get('email.gateway')) {
            $gateway = new \Tk\Mail\Gateway($this);
            $gateway->setDispatcher($this->getEventDispatcher());
            $this->set('email.gateway', $gateway);
        }
        return $this->get('email.gateway');
    }

    /**
     * getPluginFactory
     *
     * @return \Tk\Plugin\Factory
     * @throws \Tk\Db\Exception
     * @throws \Tk\Plugin\Exception
     */
    public function getPluginFactory()
    {
        if (!$this->get('plugin.factory')) {
            $this->set('plugin.factory', \Tk\Plugin\Factory::getInstance($this->getDb(), $this->getPluginPath(), $this->getEventDispatcher()));
        }
        return $this->get('plugin.factory');
    }




    /**
     * @todo This must be implemented in your \App\Config object (for now)
     * @return null|PluginApi
     */
    public function getPluginApi()
    {
        return null;
    }

    /**
     * @param int $id
     * @return \Uni\Db\InstitutionIface
     */
    abstract public function findInstitution($id);

    /**
     * @param int $id
     * @return \Uni\Db\SubjectIface
     */
    abstract public function findSubject($id);

    /**
     * @param int $id
     * @return \Uni\Db\UserIface
     */
    abstract public function findUser($id);

    /**
     * Get the Institution object for the logged in user
     *
     * @return Db\InstitutionIface
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
     * @return \App\Db\Subject|null
     * @throws \Tk\Exception
     * @todo: test this is works for all tk2uni sites
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
     */
    public function unsetSubject()
    {
        $this->getSession()->remove(self::SID_SUBJECT);
        $this->remove('subject');
    }

    /**
     * @return \Uni\Db\UserIface
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * @param Db\UserIface $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->set('user', $user);
        return $this;
    }



    //  -----------------------  Create methods  -----------------------


    /**
     * Create a page for the request
     *
     * @param \Tk\Controller\Iface $controller
     * @return Controller\Page
     */
    public function createPage($controller)
    {
        $page = new Controller\Page();
        $page->setController($controller);
        if (!$controller->getPageTitle()) {     // Set a default page Title for the crumbs
            $controller->setPageTitle($controller->getDefaultTitle());
        }
        return $page;
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
        $obj->setFieldGroupClass(\App\Form\Renderer\HorizontalFieldGroup::class);
        return $obj;
    }

    /**
     *
     * @param string $id
     * @param array $params
     * @param null|\Tk\Request $request
     * @param null|\Tk\Session $session
     * @return \Tk\Table
     */
    public static function createTable($id, $params = array(), $request = null, $session = null)
    {
        $form = \Tk\Table::create($id, $params, $request, $session);
        return $form;
    }

    /**
     * @param \Tk\Table $table
     * @return \Tk\Table\Renderer\Dom\Table
     */
    public static function createTableRenderer($table)
    {
        $obj = \Tk\Table\Renderer\Dom\Table::create($table);
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

    /**
     * Helper Method
     * Make a default HTML template to create HTML emails
     * usage:
     *  $message->setBody($message->createHtmlTemplate($bodyStr));
     *
     * @param string $body
     * @param bool $showFooter
     * @return string
     * @todo: Probably not the best place for this..... Dependant on the App
     */
    public static function createMailTemplate($body, $showFooter = true)
    {
        $request = self::getInstance()->getRequest();
        $foot = '';
        if (!self::getInstance()->isCli() && $showFooter) {
            $foot .= sprintf('<i>Page:</i> <a href="%s">%s</a><br/>', $request->getUri()->toString(), $request->getUri()->toString());
            if ($request->getReferer()) {
                $foot .= sprintf('<i>Referer:</i> <span>%s</span><br/>', $request->getReferer()->toString());
            }
            $foot .= sprintf('<i>IP Address:</i> <span>%s</span><br/>', $request->getIp());
            $foot .= sprintf('<i>User Agent:</i> <span>%s</span>', $request->getUserAgent());
        }

        $defaultHtml = sprintf('
<html>
<head>
  <title>Email</title>

<style type="text/css">
body {
  font-family: arial,sans-serif;
  font-size: 80%%;
  padding: 5px;
  background-color: #FFF;
}
table {
  font-size: 0.9em;
}
th, td {
  vertical-align: top;
}
table {

}
th {
  text-align: left;
}
td {
  padding: 4px 5px;
}
.content {
  padding: 0px 0px 0px 20px;
}
p {
  margin: 0px 0px 10px 0px;
  padding: 0px;
}
</style>
</head>
<body>
  <div class="content">%s</div>
  <p>&#160;</p>
  <hr />
  <div class="footer">
    <p>
      %s
    </p>
  </div>
</body>
</html>', $body, $foot);

        return $defaultHtml;
    }



}