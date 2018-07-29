<?php
namespace Uni\Controller\User;

use Tk\Db\Exception;
use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminEditIface
{

    /**
     * @var \Uni\Db\User
     */
    protected $user = null;

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;

    /**
     * @var \Uni\Db\Subject
     */
    protected $subject = null;



    /**
     *
     */
    public function __construct()
    {
        $this->setPageHeading();
    }

    /**
     * setPageHeading
     */
    public function setPageHeading()
    {
        switch($this->getUser()->role) {
            case \Uni\Db\User::ROLE_ADMIN:
                $this->setPageTitle('Administration User Edit');
                break;
            case \Uni\Db\User::ROLE_CLIENT:
                $this->setPageTitle('Staff/Student Edit');
                break;
            case \Uni\Db\User::ROLE_STAFF:
                $this->setPageTitle('Staff/Student Edit');
                break;
        }
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doSubject(Request $request, $subjectCode)
    {
        $this->subject = $this->getConfig()->getSubjectMapper()->findByCode($subjectCode, $this->getConfig()->getInstitutionId());
        $this->doDefault($request);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->institution = $this->getUser()->getInstitution();

        $this->user = $this->getConfig()->createUser();
        $this->user->role = $this->getUser()->role;
        if ($this->user->isClient()) {
            $this->user->role = \Uni\Db\User::ROLE_STAFF;
        }

        if ($request->has('userId')) {
            $this->user = $this->getConfig()->getUserMapper()->find($request->get('userId'));
            if (!$this->user) {
                throw new \Tk\Exception('Invalid user account.');
            }
            if ($this->institution && $this->institution->id != $this->user->getInstitution()->id) {
                throw new \Tk\Exception('Invalid user account.');
            }
        }

        $this->buildForm();
        
        $this->form->load($this->getConfig()->getUserMapper()->unmapForm($this->user));
        
        $this->form->execute();
        
    }

    /**
     * @throws \Exception
     */
    public function buildForm()
    {

        $this->form = $this->getConfig()->createForm('userEdit');
        $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));

        $tabGroup = 'Details';

        if (!$this->getuser()->isStudent()) {
            $this->form->addField(new Field\Input('username'))->setTabGroup($tabGroup)->setNotes('This is the only required field for LDAP accounts, the other fields will be automatically set on first login.')->setRequired(true);
            $this->form->addField(new Field\Input('name'))->setTabGroup($tabGroup);
        } else {
            $this->form->addField(new Field\Html('name'))->setTabGroup($tabGroup);
        }
        //$this->form->addField(new Field\Input('displayName'))->setTabGroup($tabGroup);
        if (!$this->getUser()->isStudent()) {
            $this->form->addField(new Field\Input('email'))->setTabGroup($tabGroup);
        } else {
            $this->form->addField(new Field\Html('username'))->setTabGroup($tabGroup);
            $this->form->addField(new Field\Html('email'))->setTabGroup($tabGroup);
        }
        if ($this->user->hasRole(array(\Uni\Db\User::ROLE_STAFF, \Uni\Db\User::ROLE_STUDENT))) {
            $this->form->addField(new Field\Input('uid'))->setLabel('UID')->setTabGroup($tabGroup)
                ->setNotes('The student or staff number assigned by the institution.');
        }
        if ($this->getUser()->isAdmin()) {
            if ($this->getUser()->hasRole(array(\Uni\Db\User::ROLE_STAFF, \Uni\Db\User::ROLE_CLIENT))) {
                $list = array('-- Select --' => '', 'Staff' => \Uni\Db\User::ROLE_STAFF, 'Student' => \Uni\Db\User::ROLE_STUDENT);
                $this->form->addField(new Field\Select('role', $list))->setNotes('Select the access level for this user')
                    ->setRequired(true)->setTabGroup($tabGroup);
            }
        }
        if (!$this->getuser()->isStudent() && !$this->getUser()->isStaff() && $this->getUser()->getId() != $this->user->getId()) {
            $this->form->addField(new Field\Checkbox('active'))->setTabGroup($tabGroup);
        }

        $tabGroup = 'Password';
        if ($this->user->isAdmin() || $this->user->isClient()) {
            $this->form->setAttr('autocomplete', 'off');
            $f = $this->form->addField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')
                ->setAttr('readonly', 'true')->setTabGroup($tabGroup)
                ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');");
            if (!$this->user->getId()) {
                $f->setRequired(true);
            }
            $f = $this->form->addField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')
                ->setNotes('Change this users password.')->setTabGroup($tabGroup)
                ->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');");
            if (!$this->user->getId()) {
                $f->setRequired(true);
            }
        }

        $tabGroup = 'Subjects';
        if ($this->user->id && ($this->user->isStaff() || $this->user->isClient()) ) {
            $list = \Tk\Form\Field\Option\ArrayObjectIterator::create($this->getConfig()->getSubjectMapper()->findActive($this->institution->id));
            if ($list->count()) {
                $this->form->addField(new Field\Select('selSubject[]', $list))->setLabel('Subject Selection')
                    ->setNotes('This list only shows active and enrolled subjects. Use the enrollment form in the edit subject page if your subject is not visible.')
                    ->setTabGroup($tabGroup)->addCss('tk-dualSelect')->setAttr('data-title', 'Subjects');
                $arr = $this->getConfig()->getSubjectMapper()->findByUserId($this->user->id)->toArray('id');
                $this->form->setFieldValue('selSubject', $arr);
            }
        }

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', $this->getBackUrl()));
    }

    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        $this->getConfig()->getUserMapper()->mapForm($form->getValues(), $this->user);

        // TODO: We have a unique issue here where if a user is to be created
        // TODO:  and the record has been marked deleted, then it will throw an error
        // TODO:  that the email/username, already exists. Should we locate that record
        // TODO:  and update/undelete it?

        // Password validation needs to be here
        if ($this->form->getFieldValue('newPassword')) {
            if ($this->form->getFieldValue('newPassword') != $this->form->getFieldValue('confPassword')) {
                $form->addFieldError('newPassword', 'Passwords do not match.');
                $form->addFieldError('confPassword');
            }
        }
        if (!$this->user->id && !$this->form->getFieldValue('newPassword')) {
            $form->addFieldError('newPassword', 'Please enter a new password.');
        }

        $form->addFieldErrors($this->user->validate());

        if ($form->hasErrors()) {
            return;
        }
        // Hash the password correctly
        if ($this->form->getFieldValue('newPassword')) {
            $this->user->password = $this->getConfig()->hashPassword($this->form->getFieldValue('newPassword'), $this->user);
        }

        // Add user to institution
        if ($this->institution) {
            $this->user->institutionId = $this->institution->id;

            // TODO: Add the ability to assign a staff member to subjects.
            $selected = $form->getFieldValue('selSubject');
            if ($this->user->id && is_array($selected)) {
                $list = $this->getConfig()->getSubjectMapper()->findActive($this->institution->id);
                /** @var \Uni\Db\Subject $subject */
                foreach ($list as $subject) {
                    if (in_array($subject->id, $selected)) {
                        $this->getConfig()->getSubjectMapper()->addUser($subject->id, $this->user->id);
                    } else {
                        $this->getConfig()->getSubjectMapper()->removeUser($subject->id, $this->user->id);
                    }
                }
            }
        }
        $this->user->save();

        \Tk\Alert::addSuccess('User record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save')
            $event->setRedirect(\Tk\Uri::create()->set('userId', $this->user->id));
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
        
        if ($this->user->id) {
            $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');
            $template->setChoice('update');
        } else {
            $template->insertText('username', 'Create User');
            $template->setChoice('new');
        }

        if (\Uni\Listener\MasqueradeHandler::canMasqueradeAs($this->getUser(), $this->user)) {
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Masquerade',
                \Uni\Uri::create()->reset()->set(\Uni\Listener\MasqueradeHandler::MSQ, $this->user->hash), 'fa fa-user-secret'))->addCss('tk-masquerade');
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
    <div class="panel-heading"><i class="fa fa-user fa-fw"></i> <span var="username"></span></div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}