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
     * Setup the controller to work with users of this type
     * @var string
     */
    protected $targetType = '';


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
     * @param string $targetType
     * @throws \Exception
     */
    public function doDefaultType(\Tk\Request $request, $targetType)
    {
        $this->targetType = $targetType;
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        switch($this->targetType) {
            case \Uni\Db\User::TYPE_ADMIN:
                $this->setPageTitle('Admin Users');
                break;
            case \Uni\Db\User::TYPE_STAFF:
                $this->setPageTitle('Staff Manager');
                break;
            case \Uni\Db\User::TYPE_STUDENT:
                $this->setPageTitle('Student Manager');
                break;
        }

        if (!$this->editUrl) {
            $this->editUrl = \Uni\Uri::createHomeUrl('/'.$this->targetType.'UserEdit.html');
            if ($this->getConfig()->isSubjectUrl()) {
                $this->editUrl = \Uni\Uri::createSubjectUrl('/'.$this->targetType.'UserEdit.html');
            }
        }

        $this->setTable(\Uni\Table\User::create()->setEditUrl($this->editUrl));
        if (!$this->getAuthUser()->isStudent())
            $this->getTable()->getActionCell()->removeButton('Masquerade');
        $this->getTable()->init();


        $filter = array();
        if ($this->getAuthUser()->getInstitutionId()) {
            $filter['institutionId'] = $this->getAuthUser()->getInstitutionId();
        } else if ($this->getAuthUser()->isClient()) {
            $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        }
        if (empty($filter['type'])) {
            $filter['type'] = $this->targetType;
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
        if ($this->getConfig()->getAuthUser()->hasPermission(\Uni\Db\Permission::IS_COORDINATOR) || $this->getConfig()->getAuthUser()->isClient() || $this->getConfig()->getAuthUser()->isAdmin()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Create ' . ucfirst($this->targetType), $this->getTable()->getEditUrl(), 'fa fa-user-plus'));
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