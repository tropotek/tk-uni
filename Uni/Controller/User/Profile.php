<?php
namespace Uni\Controller\User;

use Tk\Request;
use Dom\Template;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;
use Uni\Db\Permission;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Profile extends \Bs\Controller\Admin\User\Profile
{


    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->init($request);

        $this->setForm(\Uni\Form\User::create()->setModel($this->user));
        if ($this->getForm()->getField('active'))
            $this->getForm()->removeField('active');
        if ($this->getForm()->getField('username'))
            $this->getForm()->getField('username')->setAttr('disabled')->addCss('form-control disabled')->removeCss('tk-input-lock');
        if ($this->getForm()->getField('uid'))
            $this->getForm()->getField('uid')->setAttr('disabled')->addCss('form-control disabled')->removeCss('tk-input-lock');
        if ($this->getForm()->getField('email'))
            $this->getForm()->getField('email')->setAttr('disabled')->addCss('form-control disabled')->removeCss('tk-input-lock');
        $this->getForm()->removeField('selCourse');
        $this->getForm()->removeField('selSubject');

        $this->getForm()->execute();
    }




}
