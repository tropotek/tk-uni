<?php
namespace Uni\Controller\User;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaffManager extends Manager
{

    /**
     * StaffManager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageTitle('Staff Manager');
        $this->editUrl = \Uni\Uri::createHomeUrl('/staffEdit.html');
    }

    /**
     *
     * @throws \Tk\Db\Exception
     * @throws \Exception
     */
    public function initTable()
    {
        // Set List
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getUser()->getInstitution()->id;
        $filter['role'] = \Uni\Db\User::ROLE_STAFF;

        $users = \Uni\Db\UserMap::create()->findFiltered($filter, $this->table->getTool('a.name'));
        $this->table->setList($users);
    }

    /**
     * @param \Tk\Ui\Admin\ActionPanel $actionPanel
     */
    protected function initActionPanel($actionPanel)
    {
        //$actionPanel->add(\Tk\Ui\Button::create('New Student', clone $this->editUrl, 'fa fa-user-plus'));
    }


}