<?php
namespace Uni\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * @author Mick Mifsud
 * @created 2018-07-24
 * @link http://tropotek.com.au/
 * @license Copyright 2018 Tropotek
 */
class User extends \Uni\TableIface
{

    protected $roleType = '';


    /**
     * @return string
     */
    public function getRoleType()
    {
        return $this->roleType;
    }

    /**
     * @param string $roleType
     */
    public function setRoleType($roleType = '')
    {
        $this->roleType = $roleType;
    }

    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {
        $actionsCell = new \Tk\Table\Cell\Actions();
        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setAttr('data-confirm', 'You are about to masquerade as the selected user?')
            ->setOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\User */
                /* @var $button \Tk\Table\Cell\ActionButton */
                $config = \Uni\Config::getInstance();
                if ($config->getMasqueradeHandler()->canMasqueradeAs($config->getUser(), $obj)) {
                    $button->setUrl(\Uni\Uri::create()->set(\Uni\Listener\MasqueradeHandler::MSQ, $obj->getHash()));
                } else {
                    $button->setAttr('disabled', 'disabled')->addCss('disabled');
                    //$button->setVisible(false);
                }
            });
        $this->appendCell($actionsCell);
        $this->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Uni\Uri::createSubjectUrl('/userEdit.html'));
        $this->appendCell(new \Tk\Table\Cell\Email('email'));
        if (!$this->roleType) {
            $this->appendCell(new \Tk\Table\Cell\Text('roleId'))->setOnPropertyValue(function ($cell, $obj, $value) {
                /** @var \Uni\Db\User $obj */
                if ($obj->getRole())
                    $value = $obj->getRole()->getName();
                return $value;
            });
        }

        $this->appendCell(new \Tk\Table\Cell\Boolean('active'));
        $this->appendCell(\Tk\Table\Cell\Date::create('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::create('New Subject', 'fa fa-plus', \Uni\Uri::createHomeUrl('/subjectEdit.html')));
        $this->appendAction(\Tk\Table\Action\Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\User[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Uni\Db\UserMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}