<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Role extends \Bs\Db\Role
{
    
    // TODO: Rename these to DEFAULT_ID_ADMIN 

    const DEFAULT_TYPE_ADMIN    = 1;
    const DEFAULT_TYPE_CLIENT   = 2;
    const DEFAULT_TYPE_STAFF    = 3;
    const DEFAULT_TYPE_STUDENT  = 4;

    const TYPE_ADMIN    = 'admin';
    const TYPE_CLIENT   = 'client';
    const TYPE_STAFF    = 'staff';
    const TYPE_STUDENT  = 'student';
    const TYPE_PUBLIC   = 'public';


    /**
     * Role constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a default ole ID from a Type
     *
     * @param string $type Use the constants self::TYPE_ADMIN|self:TYPE_CLIENT...
     * @return int
     */
    public static function getDefaultRoleId($type)
    {
        switch($type) {
            case self::TYPE_ADMIN:
                return self::DEFAULT_TYPE_ADMIN;
            case self::TYPE_CLIENT:
                return self::DEFAULT_TYPE_CLIENT;
            case self::TYPE_STAFF:
                return self::DEFAULT_TYPE_STAFF;
            case self::TYPE_STUDENT:
                return self::DEFAULT_TYPE_STUDENT;
        }
        return 0;
    }

    /**
     * @return \Tk\Db\Map\Mapper|RoleMap
     */
    public function getMapper()
    {
        return self::createMapper();
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

        if (!$this->name) {
            $errors['name'] = 'Invalid name value';
        }

        if (!$this->type) {
            $errors['type'] = 'Invalid type value';
        }

        return $errors;
    }
}
