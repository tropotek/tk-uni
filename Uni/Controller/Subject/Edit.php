<?php
namespace Uni\Controller\Subject;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminEditIface
{
    /**
     * @var \Uni\Db\SubjectIface|\Uni\Db\Subject
     */
    protected $subject = null;

    /**
     * @var null|\Uni\Table\UserList
     */
    protected $userTable = null;

    /**
     * Edit constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Subject Edit');
    }

    /**
     * @param \Tk\Request $request
     * @return \Uni\Db\Subject|\Uni\Db\SubjectIface|null
     * @throws \Exception
     */
    protected function findSubject(\Tk\Request $request)
    {
        if (!$this->subject) {
            $this->subject = $this->getConfig()->getSubject();
            if (!$this->subject) {
                $this->subject = $this->getConfig()->createSubject();
                $this->subject->institutionId = $this->getConfig()->getInstitutionId();
                $this->subject->email = $this->getConfig()->getInstitution()->getEmail();
                if ($request->get('subjectId')) {
                    $this->subject = $this->getConfig()->getSubjectMapper()->find($request->get('subjectId'));
                    if ($this->getConfig()->getInstitutionId() != $this->subject->institutionId) {
                        \Tk\Alert::addError('You do not have permission to edit this subject.');
                        \Uni\Uri::createHomeUrl('/index.html')->redirect();
                    }
                }
            }
        }
        return $this->subject;
    }


    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->subject = $this->findSubject($request);

        $this->setForm(\Uni\Form\Subject::create()->setModel($this->subject));
        $this->initForm($request);
        $this->getForm()->execute($request);


        if ($this->subject->getId()) {
            $this->userTable = \Uni\Table\UserList::create();
            $this->userTable->setEditUrl(\Uni\Uri::createHomeUrl('/userEdit.html'));
            $this->userTable->setAjaxParams(array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'active' => 1,
                'permission' => \Uni\Db\Permission::TYPE_STUDENT
            ));
            $this->userTable->setOnSelect(function (\Tk\Request $request) {
                /** @var \Uni\Db\User $user */
                $config = \Uni\Config::getInstance();
                $data = $request->all();
                $subject = $config->getSubject();
                $user = $config->getUserMapper()->find($data['selectedId']);
                if (!$user) {
                    \Tk\Alert::addWarning('User not found!');
                } else if (!$subject) {
                    \Tk\Alert::addWarning('Subject not found!');
                } else if (!$config->getSubjectMapper()->hasUser($subject->getId(), $user->getId())) {
                    $config->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                    \Tk\Alert::addSuccess($user->getName() . ' has been linked to this Subject.');
                } else {
                    \Tk\Alert::addInfo($user->getName() . ' is already linked to this Subject.');
                }
                return \Uni\Uri::create();
            });
            $this->userTable->init();
            $filter = array(
                'id' => \Uni\Db\SubjectMap::create()->findUsers($this->subject->getId()),
                'permission' => \Uni\Db\Permission::TYPE_STUDENT
            );
            if (count($filter['id']))
                $this->userTable->setList($this->userTable->findList($filter));
        }
    }

    /**
     * @param \Tk\Request $request
     *
     * @deprecated use initForm()
     */
    protected function postInitForm(\Tk\Request $request) {
        $this->initForm($request);
        \Tk\Log::warning('Using Deprecated Method: ' . \Tk\Debug\StackTrace::dumpLine());
    }

    /**
     *
     */
    public function initActionPanel()
    {
        if ($this->subject->getId() && ($this->getUser()->isStaff() || $this->getUser()->isClient())) {
            if ($this->getUser()->hasPermission(\Uni\Db\Permission::MANAGE_SUBJECT)) {
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Plugins',
                    \Uni\Uri::createHomeUrl('/subject/' . $this->subject->getId() . '/plugins.html')->set('subjectId', $this->subject->getId()), 'fa fa-plug'));
            }
            if(!$this->getConfig()->isSubjectUrl()) {
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Enrollments',
                    \Uni\Uri::createHomeUrl('/subjectEnrollment.html')->set('subjectId', $this->subject->getId()), 'fa fa-list'));
//                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Students',
//                    \Uni\Uri::createHomeUrl('/studentUserManager.html')->set('subjectId', $this->subject->getId()), 'fa fa-group'));
            } else {
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Enrollments',
                    \Uni\Uri::createSubjectUrl('/subjectEnrollment.html'), 'fa fa-list'));
//                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Students',
//                    \Uni\Uri::createSubjectUrl('/studentUserManager.html'), 'fa fa-group'));
            }
        }
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());
        if ($this->subject->getId()) {
            $template->setAttr('panel', 'data-panel-title', "'" . $this->subject->getName() . "' [ID: "  . $this->subject->getId() . ']');
        }

        if (!$this->subject->getId()) {
            $template->setVisible('right-panel', false);
            $template->removeCss('left-panel', 'col-8')->addCss('left-panel', 'col-md-12 col-12');
        } else {
            if ($this->userTable)
                $template->appendTemplate('right-panel-01', $this->userTable->show());
        }

        return $template;
    }

    /**
     * @return \Uni\Db\Subject|null|\Uni\Db\Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="row">
  <div class="col-8" var="left-panel">
    <div class="tk-panel" data-panel-icon="fa fa-graduation-cap" var="panel"></div>
  </div>
  <div class="col-4" var="right-panel">
    <div class="tk-panel" data-panel-title="Students" data-panel-icon="fa fa-group" var="right-panel-01"></div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}