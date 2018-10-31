<?php
namespace Uni\Db;

use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use \Bs\Db\Mapper;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class UserMap extends Mapper
{
    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('institutionId', 'institution_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('roleId', 'role_id'));
            $this->dbMap->addPropertyMap(new Db\Text('uid'));
            $this->dbMap->addPropertyMap(new Db\Text('username'));
            $this->dbMap->addPropertyMap(new Db\Text('password'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('email'));
            $this->dbMap->addPropertyMap(new Db\Text('image'));
            $this->dbMap->addPropertyMap(new Db\Date('lastLogin', 'last_login'));
            $this->dbMap->addPropertyMap(new Db\Text('notes'));
            $this->dbMap->addPropertyMap(new Db\Text('sessionId', 'session_id'));
            $this->dbMap->addPropertyMap(new Db\Boolean('active'));
            $this->dbMap->addPropertyMap(new Db\Text('hash'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));
        }
        return $this->dbMap;
    }

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new \Tk\DataMap\DataMap();
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Integer('institutionId'));
            $this->formMap->addPropertyMap(new Form\Integer('roleId'));
            $this->formMap->addPropertyMap(new Form\Text('uid'));
            $this->formMap->addPropertyMap(new Form\Text('username'));
            $this->formMap->addPropertyMap(new Form\Text('password'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('email'));
            $this->formMap->addPropertyMap(new Form\Text('image'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));
            $this->formMap->addPropertyMap(new Form\Boolean('active'));
        }
        return $this->formMap;
    }

    /**
     * @param string|int $identity
     * @return \Tk\Db\Map\Model|User
     * @throws \Exception
     */
    public function findByAuthIdentity($identity)
    {
        return $this->find($identity);
    }

    /**
     * @param $username
     * @param int $institutionId
     * @param string|array $role
     * @return null|Model|User
     * @throws \Exception
     */
    public function findByUsername($username, $institutionId = 0, $role = null)
    {
        return $this->findFiltered(array(
            'institutionId' => $institutionId,
            'username' => $username,
            'type' => $role
        ))->current();
    }

    /**
     * @param string $email
     * @param int $institutionId
     * @param string|array $role
     * @return null|Model|User
     * @throws \Exception
     */
    public function findByEmail($email, $institutionId = null, $role = null)
    {
        return $this->findFiltered(array(
            'institutionId' => $institutionId,
            'email' => $email,
            'type' => $role
        ))->current();
    }

    /**
     * @param $hash
     * @param int $institutionId
     * @param string|array $role
     * @return null|Model|User
     * @throws \Exception
     */
    public function findByHash($hash, $institutionId = 0, $role = null)
    {
        return $this->findFiltered(array(
            'institutionId' => $institutionId,
            'hash' => $hash,
            'role' => $role
        ))->current();
    }

    /**
     *
     * @param int $institutionId
     * @param string|array $role
     * @param \Tk\Db\Tool|null $tool
     * @return ArrayObject|User[]
     * @throws \Exception
     */
    public function findByInstitutionId($institutionId, $role = null, $tool = null)
    {
        return $this->findFiltered(array(
            'institutionId' => $institutionId,
            'type' => $role
        ));
    }

    /**
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject|User[]
     * @throws \Exception
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        $this->makeQuery($filter, $tool, $where, $from);
        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }

    /**
     * @param array $filter
     * @param Tool $tool
     * @param string $where
     * @param string $from
     * @return $this
     */
    public function makeQuery($filter = array(), $tool = null, &$where = '', &$from = '')
    {
        $from .= sprintf('%s a ', $this->quoteParameter($this->getTable()));

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.name LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.username LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.uid LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.phone LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (!empty($filter['uid'])) {
            $where .= sprintf('a.uid = %s AND ', $this->getDb()->quote($filter['uid']));
        }

        if (!empty($filter['username'])) {
            $where .= sprintf('a.username = %s AND ', $this->getDb()->quote($filter['username']));
        }

        if (!empty($filter['email'])) {
            $where .= sprintf('a.email = %s AND ', $this->getDb()->quote($filter['email']));
        }

        if (!empty($filter['phone'])) {
            $where .= sprintf('a.phone = %s AND ', $this->getDb()->quote($filter['phone']));
        }

        if (!empty($filter['hash'])) {
            $where .= sprintf('a.hash = %s AND ', $this->getDb()->quote($filter['hash']));
        }

        if (isset($filter['institutionId'])) {
            if ($filter['institutionId'] > 0)
                $where .= sprintf('a.institution_id = %d AND ', (int)$filter['institutionId']);
            else
                $where .= sprintf('(a.institution_id IS NULL OR a.institution_id = 0) AND ');
        }

        if (!empty($filter['active'])) {
            $where .= sprintf('a.active = %s AND ', (int)$filter['active']);
        }

        if (!empty($filter['hasSession'])) {
            $where .= sprintf('a.session_id != "" AND a.session_id IS NOT NULL AND ');
        }

        if (!empty($filter['subjectId'])) {
            $from .= sprintf(', subject_has_user c');
            $where .= sprintf('a.id = c.user_id AND c.subject_id = %d AND ', (int)$filter['subjectId']);
        }

        if (!empty($filter['role']) && empty($filter['type'])) {
            $filter['type'] = $filter['role'];
        }
        if (!empty($filter['type'])) {
            $from .= sprintf(', user_role d');
            $w = $this->makeMultiQuery($filter['type'], 'd.type');
            if ($w) {
                $where .= 'a.role_id = d.id AND ('. $w . ') AND ';
            }
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) {
                $where .= '('. $w . ') AND ';
            }
        }

        if ($where) {
            $where = substr($where, 0, -4);
        }

        return $this;
    }


}