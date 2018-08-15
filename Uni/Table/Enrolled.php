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
class Enrolled extends \Uni\TableIface
{

    /**
     * @var \Uni\Ui\Dialog\AjaxSelect
     */
    protected $findSubjectDialog = null;


    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {

        $this->findSubjectDialog = new \Uni\Ui\Dialog\AjaxSelect('Migrate Student', function ($data) {
            $config = \Uni\Config::getInstance();
            $dispatcher = $config->getEventDispatcher();
            // Migrate the user to the new subject
            $event = new \Tk\Event\Event();
            $event->set('subjectFromId', $config->getSubject()->getId());
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
            \Tk\Uri::create()->reset()->set('subjectId', $config->getSubject()->getId())->redirect();
        },
            \Tk\Uri::create('/ajax/subject/findFiltered.html'));
        $this->findSubjectDialog->setAjaxParams(array('ignoreUser' => '1', 'subjectId' => $this->getConfig()->getSubject()->getId()));
        $this->findSubjectDialog->setNotes('Select the subject to migrate the student to...');
        $this->findSubjectDialog->execute(\Uni\Config::getInstance()->getRequest());



        $this->addCss('tk-enrolled-users');
        $actionsCell = new \Tk\Table\Cell\Actions();

        $btn = $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Migrate', '#', 'fa fa-exchange'));
        $btn->setAttr('data-target','#' . $this->findSubjectDialog->getId());
        $btn->setAttr('data-toggle','modal');

        $this->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->appendCell($actionsCell)->setOnCellHtml(function ($cell, $obj) {
            /** @var \Tk\Table\Cell\Actions $cell */
            /** @var \Uni\Db\User $obj */
            $config = \Uni\Config::getInstance();
            $cell->getRow()->setAttr('data-user-id', $obj->getHash());

            $btn = $cell->findButtonByName('Migrate');
            if ($btn) {
                $exclude  = $config->getSubjectMapper()->findFiltered(array('userId' => $obj->getId()))->toArray('id');
                $exclude[] = $config->getSubject()->getId();
                $list = $config->getSubjectMapper()->findFiltered(array(
                    'exclude' => $exclude
                ));
                if (count($list) && $obj->isStudent()) {
                    $btn->setAttr('data-user-id', $obj->getId());
                } else {
                    $btn->setVisible(false);
                }
            }

        });
        $this->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key');
        $this->appendCell(new \Tk\Table\Cell\Text('username'));
        $this->appendCell(new \Tk\Table\Cell\Email('email'));
        $this->appendCell(new \Tk\Table\Cell\Text('uid'));
        $this->appendCell(new \Tk\Table\Cell\Text('roleId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\User $obj */
            if ($obj->getRole())
                $value = $obj->getRole()->getName();
            return $value;
        });
        $this->appendCell(new \Tk\Table\Cell\Boolean('active'));
        $this->appendCell(new \Tk\Table\Cell\Date('created'));

        // Actions
        $this->appendAction(\Tk\Table\Action\Delete::create('delete')->setOnDelete(function (\Tk\Table\Action\Delete $action, $obj) {
            /** @var \Uni\Db\User $obj */
            $config = \Uni\Config::getInstance();
            $subject = $config->getSubject();
            $subjectMap = $config->getSubjectMapper();
            $subjectMap->removePreEnrollment($subject->getId(), $obj->getEmail());
            $subjectMap->removeUser($subject->getId(), $obj->getId());
            return false;
        }));
        $this->appendAction(\Tk\Table\Action\Csv::create());


        return $this;
    }

    /**
     * @param array $filter
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\UserIface[]
     * @throws \Exception
     */
    public function findList($filter = array())
    {
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = $this->getConfig()->getUserMapper()->findFiltered($filter, $this->getTool('name'));
        return $list;
    }

}