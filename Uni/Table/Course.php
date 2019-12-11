<?php
namespace Uni\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Course::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-12-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Course extends \Bs\TableIface
{
    
    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
    
        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('code'));
        $this->appendCell(new Cell\Text('email'));

        $this->appendCell(new Cell\Text('coordinatorId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var $obj \Uni\Db\Course */
            $value = '';
            if ($obj->getCoordinator())
                $value = $obj->getCoordinator()->getName();
            return $value;
        });
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Course', \Bs\Uri::createHomeUrl('/courseEdit.html'), 'fa fa-plus'));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());
        
        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\Course[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Uni\Db\CourseMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}