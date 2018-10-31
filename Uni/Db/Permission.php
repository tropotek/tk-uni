<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Bs\Db\Permission
{
    
    const TYPE_ADMIN            = 'type.admin';
    const TYPE_CLIENT           = 'type.client';
    const TYPE_STAFF            = 'type.staff';             // Default staff role. Has basic staff permissions.
    const TYPE_STUDENT          = 'type.student';
    const TYPE_COORDINATOR      = 'type.coordinator';       // Coordinator: Manage settings/students/staff for linked subjects.
    const TYPE_LECTURER         = 'type.lecturer';          // Lecturer: Manage student submissions/communications for linked subjects

    /**
     * Add/Edit Staff user accounts
     */
    const MANAGE_STAFF          = 'perm.manage.staff';

    /**
     * Add/Edit Student user accounts
     */
    const MANAGE_STUDENT        = 'perm.manage.student';

    /**
     * Add/Edit subject and student enrollments
     */
    const MANAGE_SUBJECT        = 'perm.manage.subject';

    /**
     * Can masquerade as other users
     */
    const CAN_MASQUERADE        = 'perm.masquerade';




}
