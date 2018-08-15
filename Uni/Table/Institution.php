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
class Institution extends \Uni\TableIface
{


    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {
        $actionsCell = new \Tk\Table\Cell\Actions();
        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                if (\Uni\Listener\MasqueradeHandler::canMasqueradeAs(\Uni\Config::getInstance()->getUser(), $obj->getUser())) {
                    $button->setUrl(\Uni\Uri::create()->set(\Uni\Listener\MasqueradeHandler::MSQ, $obj->getUser()->getHash()));
                }
            });

        $this->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->appendCell($actionsCell);
        $this->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Tk\Uri::create('admin/institutionEdit.html'));
        $this->appendCell(new \Tk\Table\Cell\Text('userId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\Institution $obj */
            $user = $obj->getUser();
            if ($user) {
                return $user->getName();
            }
            return $value;
        });
        $this->appendCell(new \Tk\Table\Cell\Email('email'));
        $this->appendCell(new \Tk\Table\Cell\Text('description'))->setCharacterLimit(64);
        $this->appendCell(new \Tk\Table\Cell\Boolean('active'));
        $this->appendCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\Institution[]
     * @throws \Exception
     */
    public function findList($filter = array())
    {
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = $this->getConfig()->getInstitutionMapper()->findFiltered($filter, $this->getTool());
        return $list;
    }

}