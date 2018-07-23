<?php
namespace Uni\Controller\User;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StudentManager extends Manager
{

    /**
     * StudentManager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setPageTitle('Student Manager');
        if (!$this->getConfig()->getSubject()) {
            $this->editUrl = \Uni\Uri::createHomeUrl('/studentEdit.html');
        } else {
            $this->editUrl = \Uni\Uri::createSubjectUrl('/studentEdit.html');
        }
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
        $filter['role'] = \Uni\Db\User::ROLE_STUDENT;

        $users = $this->getConfig()->getUserMapper()->findFiltered($filter, $this->table->getTool('a.name'));
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