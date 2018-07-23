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
class Profile extends \Uni\Controller\AdminIface
{

    /**
     * @var Form
     */
    protected $form = null;

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
        $this->getConfig()->getCrumbs()->reset();
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

        $this->form->addField(new Field\Input('displayName'))->setTabGroup('Details');
        //$this->form->addField(new Field\Input('phone'))->setTabGroup('Details');
        $this->form->addField(new Field\Input('username'))->setReadonly(true)->setTabGroup('Details');
        $this->form->addField(new Field\Input('email'))->setReadonly(true)->setTabGroup('Details');

        $this->form->addField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->addField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->addField(new Event\Link('cancel', $this->getBackUrl()));

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

        $template->insertText('username', $this->user->name . ' - [UID ' . $this->user->id . ']');

        // Render the form
        $template->insertTemplate('form', $this->form->getRenderer()->show());

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

  <div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-user fa-fw"></i> <span var="username"></span></div>
    <div class="panel-body">
      <div var="form"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($html);
    }

}