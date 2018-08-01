<?php
namespace Uni\Db;

use Tk\Db\Tool;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class RoleMap extends \Bs\Db\RoleMap
{


    /**
     * @param array $filter
     * @param Tool $tool
     * @param string $where
     * @param string $from
     * @return $this
     */
    public function makeQuery($filter = array(), $tool = null, &$where = '', &$from = '')
    {
        parent::makeQuery($filter, $tool, $where, $from);

        if (!empty($filter['institutionId'])) {
            $from .= sprintf(' LEFT JOIN user_role_institution b1 ON (a.id = b1.role_id)');
            $where .= sprintf('(b1.institution_id = %s OR (b1.institution_id IS NULL AND a.static = 1)) AND ', (int)$filter['institutionId']);
        }


        return $this;
    }



    /**
     * @param int $roleId
     * @param int $institutionId
     * @return bool
     */
    public function hasInstitution($roleId, $institutionId)
    {
        $stm = $this->getDb()->prepare('SELECT * FROM user_role_institution WHERE role_id = ? AND institution_id = ?');
        $stm->execute(array($roleId, $institutionId));
        return ($stm->rowCount() > 0);
    }

    /**
     * @param int $roleId
     * @param int $institutionId
     */
    public function addInstitution($roleId, $institutionId)
    {
        if (!$this->hasInstitution($roleId, $institutionId)) {
            $stm = $this->getDb()->prepare('INSERT INTO user_role_institution (role_id, institution_id)  VALUES (?, ?)');
            $stm->execute(array($roleId, $institutionId));
        }
    }

    /**
     * @param int $roleId
     * @param int $institutionId
     */
    public function removeInstitution($roleId, $institutionId)
    {
        if ($this->hasInstitution($roleId, $institutionId)) {
            $stm = $this->getDb()->prepare('DELETE FROM user_role_institution WHERE role_id = ? AND institution_id = ?');
            $stm->execute(array($roleId, $institutionId));
        }
    }
}