<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Bs\Db\Permission
{

    /**
     * Add/Edit Staff user accounts
     * @target staff
     */
    const MANAGE_STAFF          = 'perm.manage.staff';

    /**
     * Add/Edit Student user accounts
     * @target staff
     */
    const MANAGE_STUDENT        = 'perm.manage.student';

    /**
     * Add/Edit subject and student enrollments
     * @target staff
     */
    const MANAGE_SUBJECT        = 'perm.manage.subject';

    /**
     * Manage plugins
     * @target staff,client,admin
     */
    const MANAGE_PLUGINS        = 'perm.manage.plugins';

    /**
     * Can masquerade as other lower tier users
     * @target staff,client,admin
     */
    const CAN_MASQUERADE        = 'perm.masquerade';


    /**
     * Coordinator: Manage settings/students/staff for linked subjects.
     * @target staff
     */
    const IS_COORDINATOR        = 'perm.is.coordinator';

    /**
     * Lecturer: Manage student submissions/communications for linked subjects
     * @target staff
     */
    const IS_LECTURER           = 'perm.is.lecturer';

    /**
     * Staff member is a mentor to students
     * @target staff
     */
    const IS_MENTOR             = 'perm.is.mentor';



    /**
     * Get all available permissions for a user type
     * If type is null then all available permissions should be returns, excluding the type permissions
     *
     * @param string $type
     * @return array
     */
    public static function getPermissionList($type = '')
    {
        $arr = array(
            'Add/Edit Staff Records' => self::MANAGE_STAFF,
            'Add/Edit Student Records' => self::MANAGE_STUDENT,
            'Add/Edit Course And Subject Settings' => self::MANAGE_SUBJECT,
            'Staff Member is a Course Coordinator' => self::IS_COORDINATOR,
            'Staff Member is a Lecturer' => self::IS_LECTURER,
            'Staff Member is a Student Mentor' => self::IS_MENTOR,

            'Manage Site Plugins' => self::MANAGE_PLUGINS,
            'Can Masquerade' => self::CAN_MASQUERADE
        );
        switch ($type) {
            case User::TYPE_ADMIN;
                $arr = array(
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_CLIENT:
                $arr = array(
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STAFF:
                $arr = array(
                    'Add/Edit Staff Records' => self::MANAGE_STAFF,
                    'Add/Edit Student Records' => self::MANAGE_STUDENT,
                    'Add/Edit Course And Subject Settings' => self::MANAGE_SUBJECT,
                    'Staff Member is a Course Coordinator' => self::IS_COORDINATOR,
                    'Staff Member is a Lecturer' => self::IS_LECTURER,
                    'Staff Member is a Student Mentor' => self::IS_MENTOR,

                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STUDENT:
                $arr = array();
                break;
        }
        return $arr;
    }

    /**
     * Return the default permission set for creating new user types.
     *
     * @param string $type
     * @return array
     */
    public static function getDefaultPermissionList($type = '')
    {
        $list = self::getPermissionList($type);
        if ($type = User::TYPE_STAFF) {
            $list = array(
                'Add/Edit Student Records' => self::MANAGE_STUDENT,
                'Staff Member is a Lecturer' => self::IS_LECTURER,
                'Manage Site Plugins' => self::MANAGE_PLUGINS,
                'Can Masquerade' => self::CAN_MASQUERADE
            );
        }
        return $list;
    }
}
