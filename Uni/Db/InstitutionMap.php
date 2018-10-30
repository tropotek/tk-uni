<?php
namespace Uni\Db;

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
class InstitutionMap extends Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('domain'));
            $this->dbMap->addPropertyMap(new Db\Text('email'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Text('logo'));
            $this->dbMap->addPropertyMap(new Db\Text('feature'));

            $this->dbMap->addPropertyMap(new Db\Text('phone'));
            $this->dbMap->addPropertyMap(new Db\Text('street'));
            $this->dbMap->addPropertyMap(new Db\Text('city'));
            $this->dbMap->addPropertyMap(new Db\Text('state'));
            $this->dbMap->addPropertyMap(new Db\Text('country'));
            $this->dbMap->addPropertyMap(new Db\Text('postcode'));
            $this->dbMap->addPropertyMap(new Db\Text('address'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapLat', 'map_lat'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapLng', 'map_lng'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapZoom', 'map_zoom'));

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
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('domain'));
            $this->formMap->addPropertyMap(new Form\Text('email'));
            $this->formMap->addPropertyMap(new Form\Text('phone'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Text('logo'));
            $this->formMap->addPropertyMap(new Form\Text('feature'));
            $this->formMap->addPropertyMap(new Form\Boolean('active'));

            $this->formMap->addPropertyMap(new Form\Text('street'));
            $this->formMap->addPropertyMap(new Form\Text('city'));
            $this->formMap->addPropertyMap(new Form\Text('state'));
            $this->formMap->addPropertyMap(new Form\Text('country'));
            $this->formMap->addPropertyMap(new Form\Text('postcode'));
            $this->formMap->addPropertyMap(new Form\Text('address'));

            $this->formMap->addPropertyMap(new Form\Decimal('mapLat'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapLng'));
            $this->formMap->addPropertyMap(new Form\Integer('mapZoom'));
        }
        return $this->formMap;
    }


    /**
     *
     * @param null|\Tk\Db\Tool $tool
     * @return ArrayObject|Institution[]
     * @throws \Exception
     */
    public function findActive($tool = null)
    {
        $where = sprintf('active = 1');
        return $this->select($where, $tool);
    }

    /**
     * @param $hash
     * @param int $active
     * @return \Tk\Db\Map\Model|Institution
     * @throws \Exception
     */
    public function findByhash($hash, $active = 1)
    {
        $where = sprintf('hash = %s AND active = %s', $this->getDb()->quote($hash), (int)$active);
        return $this->select($where)->current();
    }

    /**
     * @param $domain
     * @return \Tk\Db\Map\Model|Institution
     * @throws \Exception
     */
    public function findByDomain($domain)
    {
        $where = sprintf('domain = %s', $this->getDb()->quote($domain));
        return $this->select($where)->current();
    }

    /**
     * @param int $userId
     * @return \Tk\Db\Map\Model|Institution
     * @throws \Exception
     */
    public function findByUserId($userId)
    {
        $where = sprintf('user_id = %s', (int)$userId);
        return $this->select($where)->current();
    }

    /**
     * @param array $filter
     * @param Tool $tool
     * @return ArrayObject|Institution[]
     * @throws \Exception
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        $from = sprintf('%s a ', $this->getDb()->quoteParameter($this->getTable()));
        $where = '';

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.name LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.code LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.description LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('a.city LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.state LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.country LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.postcode LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (!empty($filter['email'])) {
            $where .= sprintf('a.email = %s AND ', $this->getDb()->quote($filter['email']));
        }

        if (!empty($filter['userId'])) {
            $where .= sprintf('a.user_id = %s AND ', (int)$filter['userId']);
        }

        if (!empty($filter['active'])) {
            $where .= sprintf('a.active = %s AND ', (int)$filter['active']);
        }

        if (!empty($filter['postcode'])) {
            $where .= sprintf('a.postcode = %s AND ', $this->getDb()->quote($filter['postcode']));
        }
        if (!empty($filter['country'])) {
            $where .= sprintf('a.country = %s AND ', $this->getDb()->quote($filter['country']));
        }
        if (!empty($filter['state'])) {
            $where .= sprintf('a.state = %s AND ', $this->getDb()->quote($filter['state']));
        }
        if (!empty($filter['hash'])) {
            $where .= sprintf('a.hash = %s AND ', $this->getDb()->quote($filter['hash']));
        }
        if (!empty($filter['domain'])) {
            $where .= sprintf('a.domain = %s AND ', $this->getDb()->quote($filter['domain']));
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

        $res = $this->selectFrom($from, $where, $tool);
        return $res;
    }


}

