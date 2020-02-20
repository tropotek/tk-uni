<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Bs\Db\Permission
{


    // @deprecated constansts
    const TYPE_ADMIN            = 'type.admin';
    const TYPE_CLIENT           = 'type.client';
    const TYPE_STAFF            = 'type.staff';             // Default staff role. Has basic staff permissions.
    const TYPE_STUDENT          = 'type.student';

//    const IS_COORDINATOR      = 'type.coordinator';       // Coordinator: Manage settings/students/staff for linked subjects.
//    const IS_LECTURER         = 'type.lecturer';          // Lecturer: Manage student submissions/communications for linked subjects


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
     *
     * @param string $type
     * @return array
     */
    public static function getTypePermissionList($type = 'admin')
    {
        switch ($type) {
            case User::TYPE_ADMIN;
            case User::TYPE_CLIENT:
                return array('Can Masquerade' => self::CAN_MASQUERADE);
            case User::TYPE_STAFF:
                return array(
                    'Add/Edit Staff Records' => self::MANAGE_STAFF,
                    'Add/Edit Student Records' => self::MANAGE_STUDENT,
                    'Add/Edit Course And Subject Settings' => self::MANAGE_SUBJECT,
                    'Staff Member is a Course Coordinator' => self::IS_COORDINATOR,
                    'Staff Member is a Lecturer' => self::IS_LECTURER,
                    'Staff Member is a Student Mentor' => self::IS_MENTOR,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
        }
        return array();
    }

}
