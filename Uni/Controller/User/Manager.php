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
    protected $targetRole = 'user';


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


        $this->table = \Uni\Table\User::create()->setEditUrl($this->editUrl);
        if (!$this->getUser()->isStudent())
            $this->table->getActionCell()->removeButton('Masquerade');
        $this->table->init();


        $filter = array();
        if ($this->getUser()->getInstitution())
            $filter['institutionId'] = $this->getUser()->getInstitution()->getId();

        if (empty($filter['type'])) {
            $filter['type'] = $this->targetRole;
        }
        $this->table->setList($this->table->findList($filter));

        $this->initActionPanel($this->getActionPanel());

    }

    /**
     * @param \Tk\Ui\Admin\ActionPanel $actionPanel
     */
    protected function initActionPanel($actionPanel)
    {
        //if (!$this->getConfig()->getSession()->get('auth.password.access')) {
        if ($this->getConfig()->getUser()->hasPermission(\Uni\Db\Permission::TYPE_COORDINATOR) || $this->getConfig()->getUser()->isClient() || $this->getConfig()->getUser()->isAdmin()) {
            $actionPanel->append(\Tk\Ui\Link::createBtn('New ' . ucfirst($this->targetRole), clone $this->editUrl, 'fa fa-user-plus'));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
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
<div>
  <div class="tk-panel" data-panel-icon="fa fa-users" var="table"></div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}