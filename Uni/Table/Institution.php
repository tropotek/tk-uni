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
            ->setAttr('data-confirm', 'You are about to masquerade as the selected user?')
            ->addOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                $config = \Uni\Config::getInstance();
                if ($config->getMasqueradeHandler()->canMasqueradeAs($config->getAuthUser(), $obj->getUser())) {
                    $button->setUrl(
                        \Uni\Uri::create()
                            ->set(\Uni\Listener\MasqueradeHandler::MSQ, $obj->getUser()->getHash())
                            ->set('institutionId', '0')
                    );
                } else {
                    $button->setAttr('disabled', 'disabled')->addCss('disabled');
                    //$button->setVisible(false);
                }
            });

        $this->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->appendCell($actionsCell);
        $this->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Tk\Uri::create('admin/institutionEdit.html'));
        $this->appendCell(new \Tk\Table\Cell\Email('email'));
        //$this->appendCell(new \Tk\Table\Cell\Text('description'))->setCharacterLimit(64);
        $this->appendCell(new \Tk\Table\Cell\Boolean('active'));
        $this->appendCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setDisabled(array('id', 'name')));
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\Institution[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = $this->getConfig()->getInstitutionMapper()->findFiltered($filter, $tool);
        return $list;
    }

}