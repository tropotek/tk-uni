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

    const TYPE_COORDINATOR      = 'type.coordinator';       // Coordinator: Manage settings/students/staff for linked subjects.
    const TYPE_LECTURER         = 'type.lecturer';          // Lecturer: Manage student submissions/communications for linked subjects








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
     * Staff member is a mentor to students
     * @target staff
     */
    const STAFF_STUDENT_MENTOR  = 'perm.staff.student.mentor';

    /**
     * Can masquerade as other lower tier users
     * @target staff,client,admin
     */
    const CAN_MASQUERADE        = 'perm.masquerade';


    /**
     * @return array
     */
    public static function getAdminPermissions()
    {
        return array('Can Masquerade' => self::CAN_MASQUERADE);
    }

    /**
     * @return array
     */
    public static function getClientPermissions()
    {
        return array('Can Masquerade' => self::CAN_MASQUERADE);
    }

    /***
     * @return array
     */
    public static function getStaffPermissions()
    {
        return array(
            'Add/Edit Staff Records' => self::MANAGE_STAFF,
            'Add/Edit Student Records' => self::MANAGE_STUDENT,
            'Add/Edit Course And Subject Settings' => self::MANAGE_SUBJECT,
            'Staff Member is a Student Mentor' => self::STAFF_STUDENT_MENTOR,
            'Can Masquerade' => self::CAN_MASQUERADE
        );
    }

    /**
     * @return array
     */
    public static function getStudentPermissions()
    {
        return array();
    }


}
