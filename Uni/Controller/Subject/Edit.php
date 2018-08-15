<?php
namespace Uni\Controller\Subject;

use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Form
     */
    protected $form = null;

    /**
     * @var \Uni\Db\Subject
     */
    private $subject = null;



    /**
     * Edit constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Subject Edit');
    }

    /**
     * @param \Tk\Request $request
     * @param string $subjectCode
     * @throws \Exception
     */
    public function doSubject(\Tk\Request $request, $subjectCode)
    {
        $this->subject = $this->getConfig()->getSubjectMapper()->findByCode($subjectCode, $this->getConfig()->getInstitutionId());
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        if (!$this->subject) {
            $this->subject = $this->getConfig()->createSubject();
            $this->subject->institutionId = $this->getConfig()->getInstitutionId();
            $this->subject->email = $this->getConfig()->getInstitution()->getEmail();
            if ($request->get('subjectId')) {
                $this->subject = $this->getConfig()->getSubjectMapper()->find($request->get('subjectId'));
                if ($this->getConfig()->getInstitutionId() != $this->subject->institutionId) {
                    throw new \Tk\Exception('You do not have permission to edit this subject.');
                }
            }
        }

        $this->form = $this->getConfig()->createForm('subject-edit');
        $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));

        $this->form->appendField(new Field\Input('name'))->setRequired(true);
        $this->form->appendField(new Field\Input('code'))->setRequired(true);
        $this->form->appendField(new Field\Input('email'))->setRequired(true);
        $this->form->appendField(new Field\DateRange('date'))->setRequired(true)->setLabel('Dates')
            ->setNotes('The start and end dates of the subject. Placements cannot be created outside these dates.');
//        $this->form->appendField(new Field\Input('dateStart'))->addCss('date')->setRequired(true);
//        $this->form->appendField(new Field\Input('dateEnd'))->addCss('date')->setRequired(true);
        $this->form->appendField(new Field\Textarea('description'));

        if ($this->subject->getId()) {
            $this->form->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        }
        $this->form->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Link('cancel', $this->getBackUrl()));

        $this->form->load($this->getConfig()->getSubjectMapper()->unmapForm($this->subject));
        $this->form->execute();

    }


    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        $this->getConfig()->getSubjectMapper()->mapForm($form->getValues(), $this->subject);

        $form->addFieldErrors($this->subject->validate());

        if ($form->hasErrors()) {
            return;
        }

        $this->subject->save();

        // If this is a staff member add them to the subject
        if ($this->getUser()->isStaff()) {
            $this->getConfig()->getSubjectMapper()->addUser($this->subject->id, $this->getUser()->id);
        }

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getConfig()->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('subjectId', $this->subject->id));
        }
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('form', $this->form->getRenderer()->show());

        if ($this->subject->getId() && ($this->getUser()->isStaff() || $this->getUser()->isClient())) {
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Plugins',
                \Uni\Uri::createHomeUrl('/subject/'.$this->subject->getId().'/plugins.html'), 'fa fa-plug'));
            if(!$this->getConfig()->isSubjectUrl()) {
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Enrollments',
                    \Uni\Uri::createHomeUrl('/subjectEnrollment.html')->set('subjectId', $this->subject->getId()), 'fa fa-list'));
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Students',
                    \Uni\Uri::createHomeUrl('/studentManager.html')->set('subjectId', $this->subject->getId()), 'fa fa-group'));
            } else {
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Enrollments',
                    \Uni\Uri::createSubjectUrl('/subjectEnrollment.html'), 'fa fa-list'));
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Students',
                    \Uni\Uri::createSubjectUrl('/studentManager.html'), 'fa fa-group'));
            }
            $template->setChoice('update');
        }

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="tk-panel" data-panel-title="Subject Edit" data-panel-icon="fa fa-graduation-cap" var="form"></div>
  <!--<div class="panel panel-default">-->
    <!--<div class="panel-heading"><i class="fa fa-graduation-cap"></i> Subject Edit</div>-->
    <!--<div class="panel-body">-->
      <!--<div var="form"></div>-->
    <!--</div>-->
  <!--</div>-->
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}