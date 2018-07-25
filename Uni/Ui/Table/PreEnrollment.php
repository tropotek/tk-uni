<?php
namespace Uni\Ui\Table;

use Dom\Template;
use Tk\Db\Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PreEnrollment extends \Dom\Renderer\Renderer
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
     * @var \Uni\Ui\Dialog\PreEnrollment
     */
    protected $dialog = null;


    /**
     * constructor.
     * @param \Uni\Db\Subject $subject
     * @throws \Exception
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
        if (!$subject)
            throw new \Tk\Exception('Invalid subject details');

        $this->doDefault();
    }

    /**
     *
     * @return \Dom\Template|Template|string
     * @throws \Exception
     */
    public function doDefault()
    {
        $request = \Uni\Config::getInstance()->getRequest();

        $this->dialog = new \Uni\Ui\Dialog\PreEnrollment('Pre-Enroll User');
        $this->dialog->execute($request);

        $this->table = \Uni\Config::getInstance()->createTable('pendingUsers');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));
        $this->table->addCss('tk-pending-users');

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'))->addCss('key');
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new EnrolledCell('enrolled'));

        // Actions
        $this->table->addAction(\Tk\Table\Action\Link::create('Add', 'fa fa-plus')->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->dialog->getId()));
        $this->table->addAction(new ActionUnEnroll('delete', 'email'));
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        // Set Table List
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getUser()->getInstitution()->getId();
        $filter['subjectId'] = $this->subject->getId();

        $list = \Uni\Config::getInstance()->getSubjectMapper()->findPreEnrollments($this->subject->getId(), $this->table->makeDbTool('enrolled DESC'));

        $this->table->setList($list);

    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $ren = \Tk\Table\Renderer\Dom\Table::create($this->table);
        $ren->show();
        $this->setTemplate($ren->getTemplate());

        $template = $ren->getTemplate();

        // Enrolment Dialog
        $template->appendTemplate('tk-table', $this->dialog->show());

        $css = <<<CSS
.tk-table .tk-pending-users tr.enrolled td {
  color: #999;
}
CSS;
        $template->appendCss($css);

        return $template;
    }


    /**
     * @return \Uni\Db\User
     */
    public function getUser()
    {
        return \Uni\Config::getInstance()->getUser();
    }

    /**
     * @return \Uni\Ui\Dialog\PreEnrollment
     */
    public function getDialog()
    {
        return $this->dialog;
    }

}

class EnrolledCell extends \Tk\Table\Cell\Text
{
    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        if (!empty($obj->enrolled)) {
            $this->getRow()->addCss('enrolled');
            $this->getRow()->setAttr('data-user-id', md5($obj->user_id));
            
            $this->setAttr('title', 'User Enrolled');
            $this->setAttr('data-toggle', 'tooltip');
            $this->setAttr('data-placement', 'left');
            $this->addCss('text-center');
            return sprintf('<a href="#" class=""><i class="fa fa-check text-success"></i></a>');
        } else {
            return '';
        }
    }
}

class ActionUnEnroll extends \Tk\Table\Action\Delete
{
    /**
     * @return mixed|void
     * @throws \Exception
     */
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

        /* @var \stdClass $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (in_array($obj->email, $selected) && !in_array($obj->email, $this->excludeIdList)) {
                $subjectMap = $config->getSubjectMapper();
                $subjectMap->removePreEnrollment($obj->subject_id, $obj->email);
                /** @var \Uni\Db\Subject $subject */
                try {
                    $subject = $subjectMap->find($obj->subject_id);
                } catch (Exception $e) {
                }
                if ($subject) {  // Delete user from subject enrolment
                    $user = $config->getUserMapper()->findByEmail($obj->email, $subject->institutionId);
                    if ($user) {
                        $subjectMap->removeUser($subject->getId(), $user->getId());
                    }
                }
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
        return "'Delete ' + selected.length + ' selected records?\\nNote: Enrolled users will be removed from this subject'";
    }
}




