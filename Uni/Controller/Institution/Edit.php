<?php
namespace Uni\Controller\Institution;

use Dom\Template;
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
     * @var \Uni\Db\Institution
     */
    private $institution = null;

    /**
     * @var \Uni\Db\user
     */
    private $user = null;


    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Institution Edit');

        $this->institution = $this->getConfig()->createInstitution();
        $this->user = $this->getConfig()->createUser();
        $this->user->roleId = \Uni\Db\Role::getDefaultRoleId(\Uni\Db\Role::TYPE_CLIENT);

        if ($request->get('institutionId')) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->get('institutionId'));
            $this->user = $this->institution->getUser();
        }
        if ($this->getUser()->isClient()) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->findByUserId($this->getuser()->getId());
            $this->user = $this->institution->getUser();
        }

        $this->form = \Uni\Config::getInstance()->createForm('institutionEdit');
        $this->form->setRenderer(\Uni\Config::getInstance()->createFormRenderer($this->form));

        $this->form->appendField(new Field\Input('name'))->setRequired(true)->setTabGroup('Details');
        $this->form->appendField(new Field\Input('username'))->setRequired(true)->setTabGroup('Details');
        $this->form->appendField(new Field\Input('email'))->setRequired(true)->setTabGroup('Details');
        $this->form->appendField(new Field\File('logo', $this->institution->getDataPath().'/logo/'))
            ->setAttr('accept', '.png,.jpg,.jpeg,.gif')->setTabGroup('Details')->addCss('tk-imageinput');

        $insUrl = \Tk\Uri::create('/inst/'.$this->institution->getHash().'/login.html');
        if ($this->institution->domain)
            $insUrl = \Tk\Uri::create('/login.html')->setHost($this->institution->domain);
        $insUrlStr = $insUrl->setScheme('https')->toString();
        $this->form->appendField(new Field\Input('domain'))->setTabGroup('Details')->setNotes('Your Institution login URL is: <a href="'.$insUrlStr.'">'.$insUrlStr.'</a>' )
            ->setAttr('placeholder', $insUrl->getHost());
        $this->form->appendField(new Field\Textarea('description'))->setTabGroup('Details');
        $this->form->appendField(new Field\Checkbox('active'))->setTabGroup('Details');

        $this->form->setAttr('autocomplete', 'off');
        $f = $this->form->appendField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);
        $f = $this->form->appendField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')->setAttr('readonly', 'true')->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")->setNotes('Change this users password.')->setTabGroup('Password');
        if (!$this->user->getId())
            $f->setRequired(true);

        $this->form->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->form->appendField(new Event\Link('cancel', \Tk\Uri::create('/admin/institutionManager.html')));

        $this->form->load($this->getConfig()->getInstitutionMapper()->unmapForm($this->institution));
        $this->form->load($this->getConfig()->getUserMapper()->unmapForm($this->user));
        $this->form->load($this->institution->getData()->all());

        $this->form->execute();

    }

    /**
     * @return \Uni\Db\Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        $this->getConfig()->getInstitutionMapper()->mapForm($form->getValues(), $this->institution);
        $this->getConfig()->getUserMapper()->mapForm($form->getValues(), $this->user);
        $data = $this->institution->getData();
        $data->replace($form->getValues('/^(inst)/'));

        $form->addFieldErrors($this->institution->validate());
        $form->addFieldErrors($this->user->validate());

        /** @var \Tk\Form\Field\File $logo */
        $logo = $form->getField('logo');
        if ($logo->hasFile() && !preg_match('/\.(gif|jpe?g|png)$/i', $logo->getValue())) {
            $form->addFieldError('logo', 'Please Select a valid image file. (jpg, png, gif only)');
        }

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

        if ($form->hasErrors()) {
            return;
        }

        $logo->saveFile();
        // resize the image if needed
        if ($logo->hasFile()) {
            $fullPath = $this->getConfig()->getDataPath() . $this->institution->logo;
            \Tk\Image::create($fullPath)->bestFit(256, 256)->save();
        }

        $this->user->save();
        // Hash the password correctly
        if ($this->form->getFieldValue('newPassword')) {
            $pwd = $this->getConfig()->generatePassword(10);
            $this->user->setNewPassword($pwd);
            $this->user->save();
        }
        $this->institution->userId = $this->user->getId();
        $this->institution->save();

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getConfig()->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save')
            $event->setRedirect(\Tk\Uri::create()->set('institutionId', $this->institution->id));
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        if (\Uni\Listener\MasqueradeHandler::canMasqueradeAs($this->getUser(), $this->user)) {
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Masquerade',
                \Uni\Uri::create()->reset()->set(\Uni\Listener\MasqueradeHandler::MSQ, $this->user->hash),
                'fa fa-user-secret'))->addCss('tk-masquerade');
        }

        if ($this->getUser()->isClient() || $this->getUser()->isStaff()) {
            $this->getActionPanel()->add(\Tk\Ui\Button::create('Plugins',
                \Uni\Uri::createHomeUrl('/institution/'.$this->institution->getId().'/plugins.html'), 'fa fa-plug'));

//            $this->getActionPanel()->add(\Tk\Ui\Button::create('Roles',
//                \Uni\Uri::createHomeUrl('/roleManager.html'), 'fa fa-id-badge'));

            $this->getActionPanel()->add(\Tk\Ui\Button::create('Staff',
                \Uni\Uri::createHomeUrl('/staffManager.html'), 'fa fa-users'));

            $this->getActionPanel()->add(\Tk\Ui\Button::create('Subjects',
                \Uni\Uri::createHomeUrl('/subjectManager.html'), 'fa fa-graduation-cap'));

        }

        $template = parent::show();

        // Render the form
        $template->appendTemplate('form', $this->form->getRenderer()->show());

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
<div>

  <div class="tk-panel" data-panel-title="Institution" data-panel-icon="fa fa-university" var="form"></div>
  <!--<div class="panel panel-default">-->
    <!--<div class="panel-heading"><i class="fa fa-university fa-fw"></i> Institution</div>-->
    <!--<div class="panel-body">-->
      <!--<div var="form"></div>-->
    <!--</div>-->
  <!--</div>-->
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}