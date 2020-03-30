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
     * Add/Edit subject and student enrollments
     * @target staff
     */
    const MANAGE_SUBJECT        = 'perm.manage.subject';



    /**
     * Coordinator: Manage settings/students/staff for linked subjects.
     * Advanced access rights to student and other course/subject system data allowed
     * @target staff
     */
    const IS_COORDINATOR        = 'perm.is.coordinator';

    /**
     * Lecturer: Manage student submissions/communications for linked subjects
     * Basic access rights to view student data, no system config should be available
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
        $arr = array();
        switch ($type) {
            case User::TYPE_ADMIN;
            case User::TYPE_CLIENT:
                $arr = array(
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STUDENT:
                $arr = array();
                break;
            default:          // TYPE_STAFF
                $arr = array(
                    'Manage Staff Records' => self::MANAGE_STAFF,
                    'Course, Subject And Enrollment Settings' => self::MANAGE_SUBJECT,
                    'Staff Member is a Course Coordinator' => self::IS_COORDINATOR,
                    'Staff Member is a Lecturer' => self::IS_LECTURER,
                    'Staff Member is a Student Mentor' => self::IS_MENTOR,

                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
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
                'Staff Member is a Lecturer' => self::IS_LECTURER,
                'Manage Site Plugins' => self::MANAGE_PLUGINS,
                'Can Masquerade' => self::CAN_MASQUERADE
            );
        }
        return $list;
    }
}
