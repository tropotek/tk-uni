<?php
namespace Uni\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * @author Mick Mifsud
 * @created 2018-07-24
 * @link http://tropotek.com.au/
 * @license Copyright 2018 Tropotek
 */
class PreEnrollment extends \Uni\TableIface
{


    /**
     * @param string $tableId
     */
    public function __construct($tableId = 'pre-enrollment-table')
    {
        parent::__construct($tableId);
    }

    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {
        //$actionsCell = new \Tk\Table\Cell\Actions();
        $this->addCss('tk-pending-users');

        $this->addCell(new \Tk\Table\Cell\Checkbox('email'));
        $this->addCell(new \Tk\Table\Cell\Text('email'))->addCss('key');
        $this->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->addCell(new \Tk\Table\Cell\Boolean('enrolled'))->setOnCellHtml(function ($cell, $obj, $html) {
            /** @var $cell \Tk\Table\Cell\Boolean */
            /** @var $obj \StdClass */
            if (!empty($obj->enrolled)) {
                $cell->getRow()->addCss('enrolled');
                $cell->getRow()->setAttr('data-user-id', md5($obj->user_id));
                $cell->setAttr('title', 'User Enrolled');
                $cell->setAttr('data-toggle', 'tooltip');
                $cell->setAttr('data-placement', 'left');
                $cell->addCss('text-center');
                return sprintf('<a href="#" class=""><i class="fa fa-check text-success"></i></a>');
            }
            return '';
        });

        // Actions
        $this->addAction(\Tk\Table\Action\Link::create('Add', 'fa fa-plus')->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->dialog->getId()));
        $this->addAction(\Tk\Table\Action\Delete::create('delete', 'email')->setOnDelete(function (\Tk\Table\Action\Delete $action, $obj) {
            $config = \Uni\Config::getInstance();
            $subjectMap = $config->getSubjectMapper();
            $subjectMap->removePreEnrollment($obj->subject_id, $obj->email);
            /** @var \Uni\Db\Subject $subject */
            $subject = $subjectMap->find($obj->subject_id);
            if ($subject) {  // Delete user from subject enrolment
                $user = $config->getUserMapper()->findByEmail($obj->email, $subject->institutionId);
                if ($user) {
                    $subjectMap->removeUser($subject->getId(), $user->getId());
                }
            }
            return false;
        }));
        $this->addAction(\Tk\Table\Action\Csv::create());

        // todo: remove this one-day
        $template = $this->getRenderer()->getTemplate();
        $css = <<<CSS
.tk-table .tk-pending-users tr.enrolled td {
  color: #999;
}
CSS;
        $template->appendCss($css);

        return $this;
    }

    /**
     * @param array $filter
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Mentor[]
     * @throws \Exception
     */
    public function findList($filter = array())
    {
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Uni\Db\UserMap::create()->findFiltered($filter, $this->getTool());
        return $list;
    }

}