<?php
namespace Uni\Db;

use Tk\Db\Data;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class User extends \Bs\Db\User implements UserIface
{


    /**
     * @var int
     */
    public $institutionId = 0;


    /**
     * @var \Uni\Db\Institution
     */
    private $institution = null;



    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Get the path for all file associated to this object
     *
     * @return string
     * @throws \Exception
     */
    public function getDataPath()
    {
        return sprintf('%s/user/%s', $this->getInstitution()->getDataPath(), $this->getVolatileId());
    }

    /**
     * Helper method to generate user hash
     *
     * @param bool $isTemp
     * @return string
     * @throws \Exception
     */
    public function generateHash($isTemp = false)
    {
        $key = sprintf('%s%s%s', $this->getVolatileId(), $this->institutionId, $this->username);
        if ($isTemp) {
            $key .= date('-YmdHis');
        }
        return \Uni\Config::getInstance()->hash($key);
    }

    /**
     * Get the institution related to this user
     * @throws \Exception
     */
    public function getInstitution()
    {
        if (!$this->institution) {
            $this->institution = \Uni\Config::getInstance()->getInstitutionMapper()->find($this->institutionId);
            if (!$this->institution && $this->isClient()) {
                $this->institution = \Uni\Config::getInstance()->getInstitutionMapper()->findByUserId($this->id);
            }
        }
        return $this->institution;
    }

    /**
     * Returns true if the user is enrolled fully into the subject
     *
     * @param $subjectId
     * @return bool
     * @throws \Exception
     */
    public function isEnrolled($subjectId)
    {
        return SubjectMap::create()->hasUser($subjectId, $this->getVolatileId());
    }

    /**
     * Validate this object's current state and return an array
     * with error messages. This will be useful for validating
     * objects for use within forms.
     *
     * @return array
     * @throws \Exception
     */
    public function validate()
    {
        $errors = array();

        if (!$this->institutionId && !$this->getRole()->hasPermission(array(Permission::TYPE_ADMIN, Permission::TYPE_CLIENT))) {
            $errors['institutionId'] = 'Invalid field institutionId value';
        }

        if (!$this->roleId) {
            $errors['roleId'] = 'Invalid field roleId value';
        }

        if (!$this->username) {
            $errors['username'] = 'Invalid field username value';
        } else {
            $dup = UserMap::create()->findByUsername($this->username, $this->institutionId);
            if ($dup && $dup->getId() != $this->getId()) {
                $errors['username'] = 'This username is already in use';
            }
        }
        if ($this->email) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            } else {
                $dup = UserMap::create()->findByEmail($this->email, $this->institutionId);
                if ($dup && $dup->getId() != $this->getId()) {
                    $errors['email'] = 'This email is already in use';
                }
            }
        }
        return $errors;
    }





    /**
     * @return boolean
     * @deprecated use getRole()->hasType('..')
     */
    public function isClient()
    {
        //return $this->getRole()->hasPermission(Permission::TYPE_CLIENT);
        return $this->getRole()->hasType(Role::TYPE_CLIENT);
    }

    /**
     * @return boolean
     * @deprecated use getRole()->hasType('..')
     */
    public function isStaff()
    {
        //return $this->getRole()->hasPermission(Permission::TYPE_COORDINATOR);
        return $this->getRole()->hasType(Role::TYPE_COORDINATOR);
    }

    /**
     * @return boolean
     * @deprecated use getRole()->hasType('..')
     */
    public function isStudent()
    {
        //return $this->getRole()->hasPermission(Permission::TYPE_LECTURER);
        return $this->getRole()->hasType(Role::TYPE_STUDENT);
    }


}
