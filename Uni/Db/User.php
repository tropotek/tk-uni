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
     * @deprecated Use Role::TYPE_PUBLIC
     */
    const ROLE_PUBLIC = 'public';
    /**
     * @deprecated Use Role::TYPE_ADMIN
     */
    const ROLE_ADMIN = 'admin';
    /**
     * @deprecated Use Role::TYPE_CLIENT
     */
    const ROLE_CLIENT = 'client';
    /**
     * @deprecated Use Role::TYPE_STAFF
     */
    const ROLE_STAFF = 'staff';
    /**
     * @deprecated Use Role::TYPE_STUDENT
     */
    const ROLE_STUDENT = 'student';


    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $uid = '';

    /**
     * @var string
     */
    public $displayName = '';

    /**
     * @var string
     */
    public $image = '';


    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;

    /**
     * @var Data
     */
    protected $data = null;


    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->displayName) {
            $this->displayName = $this->name;
        }
        $this->getHash();
        $this->getData()->save();
        parent::save();
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
        if (!$this->username) {
            throw new \Tk\Exception('The object must be saved and have a valid username before generating a hash.');
        }
        $key = sprintf('%s%s%s', $this->getVolatileId(), $this->institutionId, $this->username);
        if ($isTemp) {
            $key .= date('-YmdHis');
        }
        return \App\Config::getInstance()->hash($key);
    }

    /**
     * Get the data object
     *
     * @return \Tk\Db\Data
     * @throws \Exception
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = \Tk\Db\Data::create(get_class($this), $this->getVolatileId());
        return $this->data;
    }

    /**
     * Get the institution related to this user
     * @throws \Exception
     */
    public function getInstitution()
    {
        if (!$this->institution) {
            $this->institution = InstitutionMap::create()->find($this->institutionId);
            if (!$this->institution && $this->isClient()) {
                $this->institution = InstitutionMap::create()->findByUserId($this->id);
            }
        }
        return $this->institution;
    }

    /**
     * Get a valid display name
     */
    public function getName()
    {
        if (!$this->displayName) {
            return $this->name;
        }
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * This is the institution assigned staff/student number
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return null|\Tk\Uri
     * @throws \Exception
     */
    public function getImageUrl()
    {
        if ($this->image) {
            return \Tk\Uri::create($this->getConfig()->getDataUrl() . $this->getDataPath() . $this->image);
        }
        return null;
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @return \Tk\Uri
     * @deprecated Use \Bs\Config::getInstance()->getUserHomeUrl($user)
     */
    public function getHomeUrl()
    {
        return \Uni\Config::getInstance()->getUserHomeUrl($this);
    }



    /**
     * @return boolean
     */
    public function isClient()
    {
        return $this->getRole()->hasType(Role::TYPE_CLIENT);
    }

    /**
     * @return boolean
     */
    public function isStaff()
    {
        return $this->getRole()->hasType(Role::TYPE_STAFF);
    }

    /**
     * @return boolean
     */
    public function isStudent()
    {
        return $this->getRole()->hasType(Role::TYPE_STUDENT);
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
}
