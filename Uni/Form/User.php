<?php
namespace Uni\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new User::create();
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
class User extends \Bs\Form\User
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $this->getField('update')->appendCallback(array($this, 'doSubjectUpdate'));
        $this->getField('save')->appendCallback(array($this, 'doSubjectUpdate'));


        $tab = 'Details';
        if (!$this->getConfig()->canChangePassword()) {
            $this->removeField('newPassword');
            $this->removeField('confPassword');
        } else {
            if ($this->getUser()->getId() && $this->getUser()->getId() > 1) {
                $this->appendField(new Field\Html('username'))->setAttr('disabled')->addCss('form-control disabled')->setTabGroup($tab);
            }
        }

        if ($this->getTargetRole() == \Uni\Db\Role::TYPE_STAFF) {
            $list = $this->getConfig()->getRoleMapper()->findFiltered(array(
                'type' => \Uni\Db\Role::TYPE_COORDINATOR,
                'institutionId' => $this->getConfig()->getInstitutionId()
            ));
            if ($list->count() > 1) {
                $this->appendField(Field\Select::createSelect('roleId', $list)->setTabGroup($tab)
                    ->setRequired()->prependOption('-- Select --', ''));
            }
        } else {
            $this->removeField('roleId');
        }

        $this->appendField(new Field\Input('uid'), 'username')->setLabel('UID')->addCss('tk-input-lock')->setTabGroup($tab)
            ->setNotes('The student or staff number assigned by the institution (if Applicable).');

        if ($this->getUser()->getId() == $this->getConfig()->getUser()->getId()) {
            $this->removeField('active');
        }


//        if ($this->getUser()->isStaff() || $this->getUser()->isStudent()) {
//            // TODO: This needs to be made into a searchable system as once there are many subjects it will be unmanageable
//            // TODO: This needs to be replaced with a dialog box and search feature so it works for a large number of subjects
//            // TODO: done it twice so it is becoming something that needs to be looked at soon..... ;-)
//            if ($this->getUser()->getId()) {
//                $tab = 'Subjects';
//                $list = \Tk\Form\Field\Option\ArrayObjectIterator::create($this->getConfig()->getSubjectMapper()->findFiltered(array('institutionId' => $this->getConfig()->getInstitutionId())));
//                if ($list->count()) {
//                    $this->appendField(new Field\Select('selSubject[]', $list), 'active')->setLabel('Subject Selection')
//                        ->setNotes('This list only shows active and enrolled subjects. Use the enrollment form in the edit subject page if your subject is not visible.')
//                        ->setTabGroup($tab)->addCss('tk-dualSelect')->setAttr('data-title', 'Subjects');
//                    $arr = $this->getConfig()->getSubjectMapper()->findByUserId($this->getUser()->getId())->toArray('id');
//                    $this->setFieldValue('selSubject', $arr);
//                }
//            }
//        }
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubjectUpdate($form, $event)
    {
        if ($form->hasErrors()) return;
        if (!$form->getField('selSubject')) return;

        // Add user to subjects
        $selected = $form->getFieldValue('selSubject');
        if ($this->getUser()->getId() && is_array($selected)) {
            $this->getConfig()->getSubjectMapper()->removeUser(null, $this->getUser()->getId());
            foreach ($selected as $subjectId) {
                $this->getConfig()->getSubjectMapper()->addUser($subjectId, $this->getUser()->getId());
            }
        }
        $this->getUser()->save();

    }

    /**
     * @return \Tk\Db\ModelInterface|\Uni\Db\User
     */
    public function getUser()
    {
        return $this->getModel();
    }

    /**
     * @param \Uni\Db\User $user
     * @return $this
     */
    public function setUser($user)
    {
        return $this->setModel($user);
    }


}