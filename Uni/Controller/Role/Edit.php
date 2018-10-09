<?php
namespace Uni\Controller\Role;

use Tk\Form\Event;
use Tk\Form\Field;
use Tk\ObjectUtil;

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
     * @var null|\Uni\Db\Role
     */
    protected $role = null;


    /**
     * Edit constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Role Edit');
    }

    /**
     * @param \Tk\Request $request
     * @param string $subjectCode
     * @throws \Exception
     */
    public function doSubject(\Tk\Request $request, $subjectCode)
    {
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {

        $this->role = $this->getConfig()->createRole();
        $this->role->type = $request->get('type');
        if ($request->get('roleId')) {
            $this->role = $this->getConfig()->getRoleMapper()->find($request->get('roleId'));
        }
        $this->setPageTitle(ucfirst($this->role->getType()) . ' Role Edit');

        $this->form = $this->getConfig()->createForm('role-edit');
        $this->form->setRenderer($this->getConfig()->createFormRenderer($this->form));

        $tab = 'Details';
        if ($this->role) {
            $this->form->appendField(new Field\Html('type'))->setTabGroup($tab);
        } else {
            $list = array(
                '-- Type --' => '',
                'Staff' => \Uni\Db\Role::TYPE_COORDINATOR
                //,'Student' => \Uni\Db\Role::TYPE_STUDENT
            );
            $this->form->appendField(new Field\Select('type', $list))->setLabel('Role Type')->setTabGroup($tab)->setRequired();
        }
        $this->form->appendField(new Field\Input('name'))->setTabGroup($tab)->setRequired();
        $this->form->appendField(new Field\Input('description'))->setTabGroup($tab);
        $this->form->appendField(new Field\Checkbox('active'))->setTabGroup($tab)->setNotes('Making a role inactive will result in the user having no permissions, the same permissions as the default role of its type.');

        $this->setupPermissionFields($this->form);

        $this->form->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Link('cancel', $this->getBackUrl()));

        $this->form->load(array_combine($this->role->getPermissions(), $this->role->getPermissions()));
        $this->form->load($this->getConfig()->getRoleMapper()->unmapForm($this->role));

        $this->form->execute();
        if (!$this->form->isSubmitted() && $this->role->isStatic()) {
            \Tk\Alert::addWarning('You are editing a static ROLE. These roles are set by the system and cannot be modified.');
        }

    }

    /**
     * Override for your own apps
     *
     * @param \Tk\Form $form
     * @throws \Exception
     */
    protected function setupPermissionFields($form)
    {
        $tab = 'Permission';

        $form->appendField(new Field\Checkbox(\Uni\Db\Permission::MANAGE_STAFF))->setLabel('Manage Staff')->setTabGroup($tab)
            ->setNotes('Add/Edit Staff user accounts');
        $form->appendField(new Field\Checkbox(\Uni\Db\Permission::MANAGE_STUDENT))->setLabel('Manage Students')->setTabGroup($tab)
            ->setNotes('Add/Edit Student user accounts');
        $form->appendField(new Field\Checkbox(\Uni\Db\Permission::MANAGE_SUBJECT))->setLabel('Manage Subjects')->setTabGroup($tab)
            ->setNotes('Add/Edit subject and student enrollments');
    }


    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        $this->getConfig()->getRoleMapper()->mapForm($form->getValues(), $this->role);

        $form->addFieldErrors($this->role->validate());

        if ($form->hasErrors()) {
            return;
        }

        $this->role->save();

        if ($this->role->isStatic()) {
            \Tk\Alert::addWarning('You are trying to edit a static ROLE. These roles are set by the system and cannot be modified.');
        } else {
            // Update the required permissions
            if ($this->getConfig()->getInstitutionId()) {
                $this->getConfig()->getRoleMapper()->addInstitution($this->role->getVolatileId(), $this->getConfig()->getInstitutionId());
            }

            // Save submitted permissions
            $this->role->removePermission();
            foreach ($form->getValues('/^perm\./') as $name) {
                $this->role->addPermission($name);
            }

            \Tk\Alert::addSuccess('Record saved!');
        }
        $event->setRedirect($this->getConfig()->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->reset()->set('roleId', $this->role->id));
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

  <div class="tk-panel" data-panel-title="Role Edit" data-panel-icon="fa fa-id-badge" var="form"></div>
  <!--<div class="panel panel-default">-->
    <!--<div class="panel-heading"><i class="fa fa-id-badge"></i> Role Edit</div>-->
    <!--<div class="panel-body">-->
      <!--<div var="form"></div>-->
    <!--</div>-->
  <!--</div>-->
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}