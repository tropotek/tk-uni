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
     */
    const MANAGE_STAFF            = 'perm.manage.staff';

    /**
     * Add/Edit Student user accounts
     */
    const MANAGE_STUDENT          = 'perm.manage.student';

    /**
     * Add/Edit subject and student enrollments
     */
    const MANAGE_SUBJECT          = 'perm.manage.subject';




}
