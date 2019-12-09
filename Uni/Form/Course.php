<?php
namespace Uni\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Course::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-12-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Course extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        
        $this->appendField(new Field\Select('institutionId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Select('coordinatorId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('code'));
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('email'));
        $this->appendField(new Field\Textarea('emailSignature'));
        $this->appendField(new Field\Textarea('description'));

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
        $this->load(\Uni\Db\CourseMap::create()->unmapForm($this->getCourse()));
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
        \Uni\Db\CourseMap::create()->mapForm($form->getValues(), $this->getCourse());

        // Do Custom Validations

        $form->addFieldErrors($this->getCourse()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getCourse()->getId();
        $this->getCourse()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('courseId', $this->getCourse()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Uni\Db\Course
     */
    public function getCourse()
    {
        return $this->getModel();
    }

    /**
     * @param \Uni\Db\Course $course
     * @return $this
     */
    public function setCourse($course)
    {
        return $this->setModel($course);
    }
    
}