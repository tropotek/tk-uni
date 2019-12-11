<?php
namespace Uni\Table;



/**
 * @author Mick Mifsud
 * @created 2018-07-24
 * @link http://tropotek.com.au/
 * @license Copyright 2018 Tropotek
 */
class User extends \Bs\Table\User
{

    /**
     * @var \Tk\Ui\Dialog\AjaxSelect
     */
    protected $findSubjectDialog = null;

    /**
     * @var null|array
     */
    protected $ajaxDialogParams = null;

    /**
     * @param null|array $ajaxDialogParams
     * @return User
     */
    public function setAjaxDialogParams($ajaxDialogParams)
    {
        $this->ajaxDialogParams = $ajaxDialogParams;
        return $this;
    }

    public function init()
    {
        parent::init();

        if (!$this->getConfig()->getSubject()) return $this;

        $this->findSubjectDialog = new \Tk\Ui\Dialog\AjaxSelect('Migrate Student', \Tk\Uri::create('/ajax/subject/findFiltered.html'));
        //$params = array('ignoreUser' => '1', 'subjectId' => $this->getConfig()->getSubject()->getId());
        $params = array('subjectId' => $this->getConfig()->getSubject()->getId());
        if ($this->ajaxDialogParams)
            $params = $this->ajaxDialogParams;
        $this->findSubjectDialog->setAjaxParams($params);
        $this->findSubjectDialog->setNotes('Select the subject to migrate the student to...');
        $this->findSubjectDialog->setOnSelect(function (\Tk\Request $request) {
            $config = \Uni\Config::getInstance();
            $dispatcher = $config->getEventDispatcher();
            $data = $request->all();

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
            return \Tk\Uri::create()->reset()->set('subjectId', $config->getSubject()->getId());
        });
        $this->findSubjectDialog->execute(\Uni\Config::getInstance()->getRequest());
        $template = $this->getRenderer()->getTemplate();
        $template->appendBodyTemplate($this->findSubjectDialog->show());

        $btn = $this->getActionCell()->addButton(\Tk\Table\Cell\ActionButton::create('Migrate', null, 'fa fa-exchange'));
        $btn->setAttr('data-target','#' . $this->findSubjectDialog->getId());
        $btn->setAttr('data-toggle','modal');
        $btn->setOnShow(function ($cell, $obj, $btn) use ($params) {
            /** @var \Tk\Table\Cell\Actions $cell */
            /** @var \Uni\Db\User $obj */
            /** @var \Tk\Table\Cell\ActionButton $btn */
            if ($btn->getTitle() != 'Migrate') return;
            $config = \Uni\Config::getInstance();
            if ($btn) {
                $params['exclude'] = $config->getSubject()->getId();
                $list = $config->getSubjectMapper()->findFiltered($params);
                if (count($list) && $obj->isStudent()) {
                    $btn->setAttr('data-user-id', $obj->getId());
                } else {
                    $btn->setVisible(false);
                }
            }
        });

vd();

        return $this;
    }

    /**
     * @return \Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }

    /**
     * @return \Uni\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

}