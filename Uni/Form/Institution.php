<?php
namespace Uni\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Institution::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2018-11-19
 * @link http://tropotek.com.au/
 * @license Copyright 2018 Tropotek
 */
class Institution extends \Uni\FormIface
{


    /**
     * @throws \Exception
     */
    public function init()
    {

        $tab = 'Details';
        $this->appendField(new Field\Input('name'))->setRequired(true)->setTabGroup($tab);
        $this->appendField(new Field\Input('username'))->setRequired(true)->setTabGroup($tab);
        $this->appendField(new Field\File('logo', $this->getInstitution()->getDataPath().'/logo/'))
            ->setAttr('accept', '.png,.jpg,.jpeg,.gif')->setTabGroup($tab)->addCss('tk-imageinput');
        $this->appendField(new Field\Input('email'))->setRequired(true)->setTabGroup($tab);

        $insUrl = \Tk\Uri::create('/inst/'.$this->getInstitution()->getHash().'/login.html');
        if ($this->getInstitution()->domain)
            $insUrl = \Tk\Uri::create('/login.html')->setHost($this->getInstitution()->domain);
        $insUrlStr = $insUrl->setScheme('https')->toString();
        $this->appendField(new Field\Input('domain'))->setTabGroup($tab)
            ->setNotes('Your Institution login URL is: <a href="'.$insUrlStr.'">'.$insUrlStr.'</a>')
            ->setAttr('placeholder', $insUrl->getHost());
        $this->appendField(new Field\Textarea('description'))->setTabGroup($tab);


        $tab = 'Account';
        $this->appendField(new Field\Checkbox('active'))->setTabGroup($tab)
            ->setCheckboxLabel('Institution login accounts enabled/disabled.');

        $this->setAttr('autocomplete', 'off');
        $f = $this->appendField(new Field\Password('newPassword'))->setAttr('placeholder', 'Click to edit')
            ->setAttr('readonly', 'true')
            ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")
            ->setTabGroup($tab);
        if (!$this->getInstitution()->getId())
            $f->setRequired(true);

        $f = $this->appendField(new Field\Password('confPassword'))->setAttr('placeholder', 'Click to edit')
            ->setAttr('readonly', 'true')
            ->setAttr('onfocus', "this.removeAttribute('readonly');this.removeAttribute('placeholder');")
            ->setNotes('Change this users password.')->setTabGroup($tab);
        if (!$this->getInstitution()->getId())
            $f->setRequired(true);

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
        $this->load($this->getConfig()->getUserMapper()->unmapForm($this->getInstitution()->getUser()));
        $this->load($this->getConfig()->getInstitutionMapper()->unmapForm($this->getInstitution()));
        $this->load($this->getInstitution()->getData()->all());
        parent::execute($request);
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with data from the form using a helper object
        $this->getConfig()->getInstitutionMapper()->mapForm($form->getValues(), $this->getInstitution());
        $this->getConfig()->getUserMapper()->mapForm($form->getValues(), $this->getInstitution()->getUser());
        $data = $this->getInstitution()->getData();
        $data->replace($form->getValues('/^(inst)/'));


        $form->addFieldErrors($this->getInstitution()->validate());
        $form->addFieldErrors($this->getInstitution()->getUser()->validate());

        /** @var \Tk\Form\Field\File $logo */
        $logo = $form->getField('logo');
        if ($logo->hasFile() && !preg_match('/\.(gif|jpe?g|png)$/i', $logo->getValue())) {
            $form->addFieldError('logo', 'Please Select a valid image file. (jpg, png, gif only)');
        }

        // Password validation needs to be here
        if ($form->getFieldValue('newPassword')) {
            if ($form->getFieldValue('newPassword') != $form->getFieldValue('confPassword')) {
                $form->addFieldError('newPassword', 'Passwords do not match.');
                $form->addFieldError('confPassword');
            }
        }
        if (!$this->getInstitution()->getId() && !$form->getFieldValue('newPassword')) {
            $form->addFieldError('newPassword', 'Please enter a new password.');
        }

        if ($form->hasErrors()) {
            return;
        }

        $logo->saveFile();
        // resize the image if needed
        if ($logo->hasFile()) {
            $fullPath = $this->getConfig()->getDataPath() . $this->getInstitution()->logo;
            \Tk\Image::create($fullPath)->bestFit(256, 256)->save();
        }

        $this->getInstitution()->getUser()->save();
        // Hash the password correctly
        if ($form->getFieldValue('newPassword')) {
            $pwd = $this->getConfig()->createPassword(10);
            $this->getInstitution()->getUser()->setNewPassword($pwd);
            $this->getInstitution()->getUser()->save();
        }
        //$this->getInstitution()->userId = $this->getInstitution()->getUser()->getId();
        $this->getInstitution()->save();

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save')
            $event->setRedirect(\Tk\Uri::create()->set('institutionId', $this->getInstitution()->getId()));

    }

    /**
     * @return \Tk\Db\ModelInterface|\Uni\Db\Institution
     */
    public function getInstitution()
    {
        return $this->getModel();
    }

    /**
     * @param \Uni\Db\Institution $institution
     * @return $this
     */
    public function setInstitution($institution)
    {
        return $this->setModel($institution);
    }
    
}