<?php
namespace Uni\Ui\Table;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Enrolled extends \Dom\Renderer\Renderer
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \Uni\Db\Subject
     */
    protected $subject = null;

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;


    /**
     * constructor.
     *
     * @param \Uni\Db\Subject $subject
     * @param null|\Tk\Uri $editUrl
     * @throws \Tk\Exception
     */
    public function __construct($subject, $editUrl = null)
    {
        $this->subject = $subject;
        $this->editUrl = $editUrl;
        $this->doDefault();
    }

    /**
     * @throws \Tk\Exception
     * @throws \Exception
     */
    public function doDefault()
    {

        $this->table = $this->getConfig()->createTable('enrolledUsers');
        $this->table->setRenderer($this->getConfig()->createTableRenderer($this->table));
        $this->table->addCss('tk-enrolled-users');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new ActionsCell($this->subject));
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'));
        $this->table->addCell(new NameCell('name'))->addCss('key')->setUrl($this->editUrl);
        $this->table->addCell(new \Tk\Table\Cell\Email('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Text('roleId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\User $obj */
            if ($obj->getRole())
                $value = $obj->getRole()->getName();
            return $value;
        });
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Actions
        $this->table->addAction(DeleteUser::create()->setSubject($this->subject));
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        // Set list
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->subject->institutionId;
        $filter['subjectId'] = $this->subject->getId();
        $filter['type'] = array(\Uni\Db\Role::TYPE_STAFF, \Uni\Db\Role::TYPE_STUDENT);

        $users = $this->getConfig()->getUserMapper()->findFiltered($filter, $this->table->getTool('name'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $ren = $this->table->getRenderer();
        $ren->show();
        
        /** @var \Dom\Template $template */
        $template = $ren->getTemplate();
        $this->setTemplate($template);
        
        return $template;
    }

    /**
     * @return \Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }
}

class ActionsCell extends \Tk\Table\Cell\Text
{

    /**
     * @var \Uni\Db\Subject
     */
    protected $subject = null;

    /**
     * @var \Uni\Ui\Dialog\AjaxSelect
     */
    protected $findSubjectDialog = null;

    /**
     * @param \Uni\Db\Subject $subject
     * @throws \Exception
     */
    public function __construct($subject)
    {
        parent::__construct('actions');
        $this->setOrderProperty('');
        $this->subject = $subject;
        $this->findSubjectDialog = new \Uni\Ui\Dialog\AjaxSelect('Migrate Student', array($this, 'onSelect'), \Tk\Uri::create('/ajax/subject/findFiltered.html'));
        $this->findSubjectDialog->setAjaxParams(array('ignoreUser' => '1', 'subjectId' => $this->subject->getId()));
        $this->findSubjectDialog->setNotes('Select the subject to migrate the student to...');
        $this->findSubjectDialog->execute(\Uni\Config::getInstance()->getRequest());
    }

    /**
     * @param \Tk\Table $table
     * @return ActionsCell|\Tk\Table\Cell\Text
     * @throws \Exception
     */
    public function setTable($table)
    {
        $ren = $table->getRenderer();
        if ($ren) {
            /** @var \Dom\Template $tableTemplate */
            $tableTemplate = $ren->getTemplate();
            $tableTemplate->appendTemplate('tk-table', $this->findSubjectDialog->show());
        }
        return parent::setTable($table);
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function onSelect($data)
    {
        $config = \Uni\Config::getInstance();

        $dispatcher = $config->getEventDispatcher();
        // Migrate the user to the new subject
        $event = new \Tk\Event\Event();
        $event->set('subjectFromId', $this->subject->getId());
        $event->set('subjectToId', $data['selectedId']);
        $event->set('userId', $data['userId']);
        $dispatcher->dispatch(\Uni\UniEvents::SUBJECT_MIGRATE_USER, $event);
        
        if (!$event->isPropagationStopped()) {
            /** @var \Uni\Db\User $user */
            $user = $config->getUserMapper()->find($event->get('userId'));
            if ($user) {
                if ($config->getSubjectMapper()->hasUser($event->get('subjectFromId'), $user->getId())) {
                    $config->getSubjectMapper()->removeUser($event->get('subjectFromId'), $user->getId());
                    // delete user from the pre-enrolment list if exists
                    $config->getSubjectMapper()->removePreEnrollment($event->get('subjectFromId'), $user->email);
                }
                if (!$config->getSubjectMapper()->hasUser($event->get('subjectToId'), $user->getId())) {
                    $config->getSubjectMapper()->addUser($event->get('subjectToId'), $user->getId());
                }
            }
        }
        
        \Tk\Uri::create()->reset()->set('subjectId', $this->subject->getId())->redirect();
    }

    /**
     * @param \Uni\Db\User $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     * @throws \Exception
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $config = \Uni\Config::getInstance();
        $template = $this->__makeTemplate();

        // exclude any subjects already enrolled in
        $enrolledList  = $config->getSubjectMapper()->findFiltered(array('userId' => $obj->getId()));
        $exclude = array($this->subject->getId());
        foreach ($enrolledList as $subject) {
            $exclude[] = $subject->getId();
        }
        $list = $config->getSubjectMapper()->findFiltered(array(
            'exclude' => $exclude
        ));

        if (count($list) && $obj->isStudent()) {
            $template->setAttr('migrate', 'data-target', '#' . $this->findSubjectDialog->getId());
            $template->setAttr('migrate', 'data-toggle', 'modal');
            $template->setAttr('migrate', 'data-user-id', $obj->getId());
            $template->setChoice('migrate');
        }

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="">
  <a href="#" class="btn btn-default btn-xs migrateUser" title="Migrate user to another subject " var="migrate" choice="migrate"><i class="fa fa-exchange"></i></a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }

}

class NameCell extends \Tk\Table\Cell\Text
{
    public function getCellHtml($obj, $rowIdx = null)
    {
        $this->getRow()->setAttr('data-user-id', md5($obj->getId()) );
        return parent::getCellHtml($obj, $rowIdx);
    }
}

class DeleteUser extends \Tk\Table\Action\Delete
{
    /** @var \Uni\Db\Subject null */
    private $subject = null;

    /**
     * @param \Uni\Db\Subject  $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    
    public function execute()
    {
        $config = \Uni\Config::getInstance();
        $request = $this->getTable()->getRequest();
        if (empty($request[$this->checkboxName])) {
            return;
        }
        $selected = $request[$this->checkboxName];
        if (!is_array($selected)) return;
        $i = 0;

        $subjectId = $this->subject->getId();
        /* @var \Uni\Db\User $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (in_array($obj->getId(), $selected) && !in_array($obj->getId(), $this->excludeIdList)) {
                $subjectMap = $config->getSubjectMapper();
                $subjectMap->removePreEnrollment($subjectId, $obj->email);
                $subjectMap->removeUser($subjectId, $obj->getId());
                $i++;
            }
        }
        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey($this->getName()))->redirect();
    }

    /**
     * @return string
     */
    protected function getConfirmStr()
    {
        return "'Delete ' + selected.length + ' selected records?\\nNote: Users will be removed from this subject and the pending-enrollment list.'";
    }
}