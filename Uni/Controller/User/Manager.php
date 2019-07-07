<?php
namespace Uni\Controller\User;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Uni\Controller\AdminManagerIface
{

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;

    /**
     * Setup the controller to work with users of this role
     * @var string
     */
    protected $targetRole = '';


    /**
     * Manager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('User Manager');
    }

    /**
     * @param \Tk\Request $request
     * @param string $targetRole
     * @throws \Exception
     */
    public function doDefaultRole(\Tk\Request $request, $targetRole)
    {
        $this->targetRole = $targetRole;
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        switch($this->targetRole) {
            case \Uni\Db\Role::TYPE_ADMIN:
                $this->setPageTitle('Admin Users');
                break;
            case \Uni\Db\Role::TYPE_COORDINATOR:
                $this->setPageTitle('Staff Manager');
                break;
            case \Uni\Db\Role::TYPE_STUDENT:
                $this->setPageTitle('Student Manager');
                break;
        }

        if (!$this->editUrl) {
            $this->editUrl = \Uni\Uri::createHomeUrl('/'.$this->targetRole.'UserEdit.html');
            if ($this->getConfig()->isSubjectUrl()) {
                $this->editUrl = \Uni\Uri::createSubjectUrl('/'.$this->targetRole.'UserEdit.html');
            }
        }

        $this->setTable(\Uni\Table\User::create()->setEditUrl($this->editUrl));
        if (!$this->getUser()->isStudent())
            $this->getTable()->getActionCell()->removeButton('Masquerade');
        $this->getTable()->init();


        $filter = array();
        if ($this->getUser()->institutionId) {
            $filter['institutionId'] = $this->getUser()->institutionId;
        } else if ($this->getUser()->isClient()) {
            $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        }
        if (empty($filter['type'])) {
            $filter['type'] = $this->targetRole;
        }
        if (($this->getConfig()->isSubjectUrl() || $request->has('subjectId')) && $this->getConfig()->getSubjectId()) {
            $filter['subjectId'] = $this->getConfig()->getSubjectId();
        }
        $this->getTable()->setList($this->getTable()->findList($filter));


    }

    /**
     *
     */
    public function initActionPanel()
    {
        //if (!$this->getConfig()->getSession()->get('auth.password.access')) {
        if ($this->getConfig()->getUser()->hasPermission(\Uni\Db\Permission::TYPE_COORDINATOR) || $this->getConfig()->getUser()->isClient() || $this->getConfig()->getUser()->isAdmin()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Create ' . ucfirst($this->targetRole), $this->getTable()->getEditUrl(), 'fa fa-user-plus'));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('table', $this->table->show());

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-icon="fa fa-users" var="table"></div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}