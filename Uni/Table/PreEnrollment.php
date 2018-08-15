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
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {
        //$actionsCell = new \Tk\Table\Cell\Actions();
        $this->addCss('tk-pending-users');

        $this->appendCell(new \Tk\Table\Cell\Checkbox('email'));
        $this->appendCell(new \Tk\Table\Cell\Text('email'))->addCss('key');
        $this->appendCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->appendCell(new \Tk\Table\Cell\Boolean('enrolled'))->setOnCellHtml(function ($cell, $obj, $html) {
            /** @var $cell \Tk\Table\Cell\Boolean */
            /** @var $obj \StdClass */
            $config = \Uni\Config::getInstance();
            $cell->getRow()->setAttr('data-user-id', $obj->hash);

            if (!empty($obj->enrolled)) {
                $cell->getRow()->addCss('enrolled');
                $cell->setAttr('title', 'User Enrolled');
                $cell->setAttr('data-toggle', 'tooltip');
                $cell->setAttr('data-placement', 'left');
                $cell->addCss('text-center');
                return sprintf('<a href="#" class=""><i class="fa fa-check text-success"></i></a>');
            }
            return '';
        });

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::create('Add', 'fa fa-plus')->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->dialog->getId()));
        $this->appendAction(\Tk\Table\Action\Delete::create('delete', 'email')->setOnDelete(function (\Tk\Table\Action\Delete $action, $obj) {
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
        $this->appendAction(\Tk\Table\Action\Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @return array
     * @throws \Exception
     */
    public function findList($filter = array())
    {
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = $this->getConfig()->getSubjectMapper()->findPreEnrollments($filter, $this->getTool('enrolled DESC'));
        return $list;
    }

}