<?php
namespace Uni\Controller\User;

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
class Profile extends \Uni\Controller\AdminEditIface
{

    /**
     * @var \Uni\Db\User
     */
    private $user = null;

    /**
     * Profile constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('My Profile');
        //$this->getConfig()->getCrumbs()->reset();
    }

    /**
     *
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        
        $this->user = $this->getUser();

        $this->form = \Uni\Config::getInstance()->createForm('userEdit');
        $this->form->setRenderer(\Uni\Config::getInstance()->createFormRenderer($this->form));
        $this->form->setAttr('autocomplete', 'off');

        $tab = 'Details';
        $this->form->appendField(new Field\Html('username'))->setTabGroup($tab);
        $this->form->appendField(new Field\Input('name'))->setTabGroup($tab);
        if ($this->getConfig()->canChangePassword()) {
            $this->form->appendField(new Field\Input('email'))->setTabGroup($tab);
        } else {
            $this->form->appendField(new Field\Html('email'))->setTabGroup($tab);
        }

        $tab = 'Password';
        if ($this->getConfig()->canChangePassword()) {
            $this->form->setAttr('autocomplete', 'off');
            $f = $this->form->appendField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')
                ->setAttr('readonly', 'true')->setTabGroup($tab)
                ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');");
            if (!$this->user->getId()) {
                $f->setRequired(true);
            }
            $f = $this->form->appendField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')
                ->setNotes('Change this users password.')->setTabGroup($tab)->setAttr('readonly', 'true')
                ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');");
            if (!$this->user->getId()) {
                $f->setRequired(true);
            }
        }

        $this->form->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Link('cancel', $this->getBackUrl()));

        $this->form->load($this->getConfig()->getUserMapper()->unmapForm($this->user));
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
        $this->getConfig()->getUserMapper()->mapForm($form->getValues(), $this->user);

        $form->addFieldErrors($this->user->validate());

        if ($form->hasErrors()) {
            return;
        }

        if ($this->form->getFieldValue('newPassword')) {
            $this->user->setNewPassword($this->form->getFieldValue('newPassword'));
        }

        $this->user->save();

        \Tk\Alert::addSuccess('User record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create());
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('form', $this->form->getRenderer()->show());

        if ($this->user->id) {
            $template->setAttr('form', 'data-panel-title', $this->user->name . ' - [UID ' . $this->user->id . ']');
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

        $html = <<<HTML
<div class="">
  <div class="tk-panel" data-panel-icon="fa fa-user" var="form"></div>
</div>
HTML;

        return \Dom\Loader::load($html);
    }

}