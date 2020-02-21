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
     * @deprecated use $user->isAdmin() or $user->hasType(User::TYPE_ADMIN)
     * @remove 4.0.0
     */
    const TYPE_ADMIN            = 'type.admin';
    /**
     * @deprecated use $user->isAdmin() or $user->hasType(User::TYPE_ADMIN)
     * @remove 4.0.0
     */
    const TYPE_CLIENT           = 'type.client';
    /**
     * @deprecated use $user->isAdmin() or $user->hasType(User::TYPE_ADMIN)
     * @remove 4.0.0
     */
    const TYPE_STAFF            = 'type.staff';
    /**
     * @deprecated use $user->isAdmin() or $user->hasType(User::TYPE_ADMIN)
     * @remove 4.0.0
     */
    const TYPE_STUDENT          = 'type.student';



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
     * Can masquerade as other lower tier users
     * @target staff,client,admin
     */
    const CAN_MASQUERADE        = 'perm.masquerade';


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
            'Can Masquerade' => self::CAN_MASQUERADE
        );
        switch ($type) {
            case User::TYPE_ADMIN;
                $arr = array(
                    'Type Is Administrator' => self::TYPE_ADMIN,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_CLIENT:
                $arr = array(
                    'Type Is Institution Client' => self::TYPE_CLIENT,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STAFF:
                $arr = array(
                    'Type Is Staff' => self::TYPE_STAFF,
                    'Add/Edit Staff Records' => self::MANAGE_STAFF,
                    'Add/Edit Student Records' => self::MANAGE_STUDENT,
                    'Add/Edit Course And Subject Settings' => self::MANAGE_SUBJECT,
                    'Staff Member is a Course Coordinator' => self::IS_COORDINATOR,
                    'Staff Member is a Lecturer' => self::IS_LECTURER,
                    'Staff Member is a Student Mentor' => self::IS_MENTOR,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STUDENT:
                $arr = array(
                    'Type Is Student' => self::TYPE_STUDENT
                );
                break;
        }

        // TODO: @remove 4.0.0
        if (true) { // This removes any type permissions as they are deprecated for v4.0
            $a = array();
            foreach ($arr as $k => $v) {
                if (!preg_match('/^type\./', $v))
                    $a[$k] = $v;
            }
            $arr = $a;
        }
        return $arr;
    }

}
