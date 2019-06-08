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
        $this->getForm()->removeField('selSubject');
        $this->getForm()->removeField('active');
        $this->getForm()->removeField('uid');
        $this->getForm()->removeField('roleId');
        $this->getForm()->getField('email')->setAttr('disabled')->addCss('form-control disabled');
        $this->getForm()->execute();
    }




}