<?php
namespace Uni\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Subject::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-06-04
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Subject extends \Uni\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getRenderer()->getLayout();
        $layout->addRow('name', 'col-md-6');
        $layout->removeRow('code', 'col-md-6');
        $layout->addRow('publish', 'col-md-6');
        $layout->removeRow('notify', 'col-md-6');

        $this->appendField(new Field\Input('name'))->setRequired(true);
        $this->appendField(new Field\Input('code'))->setRequired(true);
        $this->appendField(new Field\Input('email'))->setRequired(true);
        $this->appendField(new Field\DateRange('date'))->setRequired(true)->setLabel('Dates')
            ->setNotes('The start and end dates of the subject. Student actions will be restricted outside these dates.');

        $this->appendField(new Field\Checkbox('publish'))
            ->setCheckboxLabel('If not set, students will not be able to access this subject and its data.');
        $this->appendField(new Field\Checkbox('notify'))
            ->setCheckboxLabel('Use this setting to disable email notifications for the entire subject.');

        $this->appendField(new Field\Textarea('description'))->addCss('tkTextareaTool');


        if ($this->getSubject()->getId())
            $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load($this->getConfig()->getSubjectMapper()->unmapForm($this->getSubject()));
        parent::execute($request);
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with form data
        $this->getConfig()->getSubjectMapper()->mapForm($form->getValues(), $this->getSubject());

        // Do Custom Validations

        $form->addFieldErrors($this->getSubject()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getSubject()->getId();
        $this->getSubject()->save();

        // If this is a staff member add them to the subject
        if ($this->getUser()->isStaff()) {
            $this->getConfig()->getSubjectMapper()->addUser($this->getSubject()->getId(), $this->getUser()->getId());
        }

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $url = \Tk\Uri::create()->set('subjectId', $this->getSubject()->getId());
            if ($this->getConfig()->isSubjectUrl())
                $url = \Uni\Uri::create();
            $event->setRedirect($url);
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Uni\Db\SubjectIface
     */
    public function getSubject()
    {
        return $this->getModel();
    }

    /**
     * @param \Uni\Db\SubjectIface $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        return $this->setModel($subject);
    }
    
}