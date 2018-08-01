<?php
namespace Uni\Db;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
interface RoleIface extends \Bs\Db\RoleIface
{

    /**
     * @return InstitutionIface
     */
    public function getInstitution();



}