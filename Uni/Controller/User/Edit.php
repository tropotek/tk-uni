<?php
namespace Uni\Controller\User;

use Tk\Request;
use Dom\Template;
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
     * Setup the controller to work with users of this role
     * @var string
     */
    protected $targetRole = '';

    /**
     * @var \Uni\Db\User
     */
    protected $user = null;



    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('User Edit');
    }

    /**
     * @param \Tk\Request $request
     * @param string $subjectCode
     * @param string $targetRole
     * @throws \Exception
     */
    public function doSubject(\Tk\Request $request, $subjectCode, $targetRole)
    {
        $this->doDefaultRole($request, $targetRole);
    }

    /**
     * @param \Tk\Request $request
     * @param string $targetRole
     * @throws \Exception
     */
    public function doDefaultRole(\Tk\Request $request, $targetRole)
    {
        $this->targetRole = $targetRole;
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        switch($this->targetRole) {
            case \Uni\Db\Role::TYPE_ADMIN:
                $this->setPageTitle('Admin Edit');
                break;
            case \Uni\Db\Role::TYPE_STAFF:
                $this->setPageTitle('Staff Edit');
                break;
            case \Uni\Db\Role::TYPE_STUDENT:
                $this->setPageTitle('Student Edit');
                break;
        }


        $this->user = $this->getConfig()->createUser();
        if ($this->targetRole == \Uni\Db\Role::TYPE_STUDENT || $this->targetRole == \Uni\Db\Role::TYPE_STAFF) {
            $this->user->institutionId = $this->getConfig()->getInstitutionId();
        }
        $this->user->roleId = \Uni\Db\Role::getDefaultRoleId($this->targetRole);

        if ($request->has('userId')) {
            $this->user = $this->getConfig()->getUserMapper()->find($request->get('userId'));
            if (!$this->user)
                throw new \Tk\Exception('Invalid user account.');
            if ($this->getUser()->isStaff() && $this->getUser()->institutionId != $this->user->institutionId)
                throw new \Tk\Exception('Invalid system details');
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

        $list = \Uni\Db\RoleMap::create()->findFiltered(array('type' => \Uni\Db\Role::TYPE_STAFF, 'institutionId' => $this->getConfig()->getInstitutionId()));
        if ($this->targetRole == \Uni\Db\Role::TYPE_STAFF && $list->count() > 1) {
            $this->form->appendField(Field\Select::createSelect('roleId', $list)->setTabGroup($tabGroup)->setRequired()->prependOption('-- Select --', ''));
        }

        if (!$this->user->getId() || ($this->getConfig()->canChangePassword() && $this->user->getId() != 1)) {
            $this->form->appendField(new Field\Input('username'))->setTabGroup($tabGroup)->setRequired(true);
        } else {
            $this->form->appendField(new Field\Html('username'))->setTabGroup($tabGroup);
        }

        $this->form->appendField(new Field\Input('email'))->setTabGroup($tabGroup)->setRequired();
        $this->form->appendField(new Field\Input('name'))->setTabGroup($tabGroup);
        //$this->form->appendField(new Field\Input('displayName'))->setTabGroup($tabGroup);

        if ($this->targetRole != \Uni\Db\Role::TYPE_STAFF || $this->targetRole != \Uni\Db\Role::TYPE_STUDENT) {
            $this->form->appendField(new Field\Input('uid'))->setLabel('UID')->setTabGroup($tabGroup)
                ->setNotes('The student or staff number assigned by the institution (if Applicable).');
        }
        if($this->getUser()->getId() != $this->user->getId()){
            $this->form->appendField(Field\Checkbox::create('active')->setCheckboxLabel('Enable/Disable user login.'))->setTabGroup($tabGroup);
        }

        $tabGroup = 'Subjects';

        if ($this->user->id && ($this->user->isStaff() || $this->user->isStudent()) ) {
            // TODO: This needs to be made into a searchable system as once there are many subjects it will be unmanageable
            // TODO: This needs to be replaced with a dialog box and search feature so it works for a large number of subjects
            // TODO: done it twice so it is becoming something that needs to be looked at soon..... ;-)
            $list = \Tk\Form\Field\Option\ArrayObjectIterator::create($this->getConfig()->getSubjectMapper()->findFiltered(array('institutionId' => $this->getConfig()->getInstitutionId())));
            if ($list->count()) {
                $this->form->appendField(new Field\Select('selSubject[]', $list))->setLabel('Subject Selection')
                    ->setNotes('This list only shows active and enrolled subjects. Use the enrollment form in the edit subject page if your subject is not visible.')
                    ->setTabGroup($tabGroup)->addCss('tk-dualSelect')->setAttr('data-title', 'Subjects');
                $arr = $this->getConfig()->getSubjectMapper()->findByUserId($this->user->id)->toArray('id');
                $this->form->setFieldValue('selSubject', $arr);
            }
        }

        $tabGroup = 'Password';
        if ($this->getConfig()->canChangePassword()) {
            $this->form->setAttr('autocomplete', 'off');
            $f = $this->form->appendField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')
                ->setAttr('readonly', 'true')->setTabGroup($tabGroup)
                ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');");
            if (!$this->user->getId()) {
                $f->setRequired(true);
            }
            $f = $this->form->appendField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')
                ->setNotes('Change this users password.')->setTabGroup($tabGroup)->setAttr('readonly', 'true')
                ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');");
            if (!$this->user->getId()) {
                $f->setRequired(true);
            }
        }

        $this->form->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Link('cancel', $this->getBackUrl()));
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

        // Password validation needs to be here
        if ($this->form->getFieldValue('newPassword')) {
            if ($this->form->getFieldValue('newPassword') != $this->form->getFieldValue('confPassword')) {
                $form->addFieldError('newPassword', 'Passwords do not match.');
                $form->addFieldError('confPassword');
            }
        }
        if (!$this->user->getId() && !$this->form->getFieldValue('newPassword')) {
            $form->addFieldError('newPassword', 'Please enter a new password.');
        }

        $form->addFieldErrors($this->user->validate());

        if ($form->hasErrors()) {
            return;
        }

        if ($this->form->getFieldValue('newPassword')) {
            $this->user->setNewPassword($this->form->getFieldValue('newPassword'));
        }

        // Add user to subjects
        $selected = $form->getFieldValue('selSubject');
        if ($this->user->getId() && is_array($selected)) {
            $this->getConfig()->getSubjectMapper()->removeUser(null, $this->user->getId());
            foreach ($selected as $subjectId) {
                $this->getConfig()->getSubjectMapper()->addUser($subjectId, $this->user->getId());
            }
        }

        $this->user->save();

        \Tk\Alert::addSuccess('User record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save')
            $event->setRedirect(\Tk\Uri::create()->set('userId', $this->user->getId()));
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        if ($this->user->getId() && \Uni\Listener\MasqueradeHandler::canMasqueradeAs($this->getUser(), $this->user)) {
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Masquerade',
                \Uni\Uri::create()->reset()->set(\Uni\Listener\MasqueradeHandler::MSQ, $this->user->hash), 'fa fa-user-secret'))
                ->setAttr('data-confirm', 'You are about to masquerade as the selected user?')->addCss('tk-masquerade');
        }

        $template = parent::show();

        // Render the form
        $template->appendTemplate('form', $this->form->getRenderer()->show());
        
        if ($this->user->id) {
            $template->setAttr('form', 'data-panel-title', $this->user->name . ' - [UID ' . $this->user->getId() . ']');
        } else {
            $template->setAttr('form', 'data-panel-title', 'Create User');
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

  <div class="tk-panel" data-panel-title="User Edit" data-panel-icon="fa fa-user" var="form"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}