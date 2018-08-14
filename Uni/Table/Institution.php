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

        $this->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->addCell($actionsCell);
        $this->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Tk\Uri::create('admin/institutionEdit.html'));
        $this->addCell(new \Tk\Table\Cell\Text('userId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\Institution $obj */
            $user = $obj->getUser();
            if ($user) {
                return $user->getName();
            }
            return $value;
        });
        $this->addCell(new \Tk\Table\Cell\Email('email'));
        $this->addCell(new \Tk\Table\Cell\Text('description'))->setCharacterLimit(64);
        $this->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->addAction(\Tk\Table\Action\Delete::create());
        $this->addAction(\Tk\Table\Action\Csv::create());

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