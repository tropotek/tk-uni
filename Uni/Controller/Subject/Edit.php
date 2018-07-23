<?php
namespace Uni\Controller\Subject;

use Dom\Template;
use Tk\Db\Exception;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminIface
{

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \Uni\Db\Subject
     */
    private $subject = null;

    /**
     * @var \Uni\Db\Institution
     */
    private $institution = null;

    protected $isSubjectPage = false;


    /**
     * Edit constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Subject Edit');
    }

    /**
     * @param Request $request
     * @param string $subjectCode
     * @throws \Exception
     */
    public function doSubject(Request $request, $subjectCode)
    {
        $this->isSubjectPage = true;
        $this->subject = $this->getConfig()->getSubjectMapper()->findByCode($subjectCode, $this->getConfig()->getInstitutionId());
        if ($this->subject)
            $this->institution = $this->subject->getInstitution();
        $this->doDefault($request);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        if (!$this->subject) {
            $this->institution = $this->getUser()->getInstitution();
            $this->subject = $this->getConfig()->createSubject();
            $this->subject->institutionId = $this->institution->id;
            if ($request->get('subjectId')) {
                $this->subject = $this->getConfig()->getSubjectMapper()->find($request->get('subjectId'));
                if ($this->institution->id != $this->subject->institutionId) {
                    throw new \Tk\Exception('You do not have permission to edit this subject.');
                }
            }
        }

        $this->form = $this->getConfig()->createForm('subjectEdit');
        $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));

        $this->form->addField(new Field\Input('name'))->setRequired(true);
        $this->form->addField(new Field\Input('code'))->setRequired(true);
        $this->form->addField(new Field\Input('email'))->setRequired(true);
        $this->form->addField(new Field\DateRange('date'))->setRequired(true)->setLabel('Dates')
            ->setNotes('The start and end dates of the subject. Placements cannot be created outside these dates.');
//        $this->form->addField(new Field\Input('dateStart'))->addCss('date')->setRequired(true);
//        $this->form->addField(new Field\Input('dateEnd'))->addCss('date')->setRequired(true);
        $this->form->addField(new Field\Textarea('description'));

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', $this->getBackUrl()));

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
        if ($this->getUser()->hasRole(\Uni\Db\User::ROLE_STAFF)) {
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
        $template->insertTemplate('form', $this->form->getRenderer()->show());

        if ($this->subject->id && ($this->getUser()->isStaff() || $this->getUser()->isClient())) {
            if(!$this->isSubjectPage) {
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Enrollments',
                    \Uni\Uri::createHomeUrl('/subjectEnrollment.html')->set('subjectId', $this->subject->id), 'fa fa-list'));
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Students',
                    \Uni\Uri::createHomeUrl('/studentManager.html')->set('subjectId', $this->subject->id), 'fa fa-group'));
            } else {
                $this->getActionPanel()->add(\Tk\Ui\Button::create('Enrollments',
                    \Uni\Uri::createSubjectUrl('/subjectEnrollment.html'), 'fa fa-list'));
            }
            $template->setChoice('update');
        }

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-graduation-cap"></i> Subject Edit</div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}