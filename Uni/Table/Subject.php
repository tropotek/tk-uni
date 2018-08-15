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
class Subject extends \Uni\TableIface
{


    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key');
        $this->appendCell(new \Tk\Table\Cell\Text('code'));
        //$this->appendCell(new \Tk\Table\Cell\Email('email'));
        //$this->appendCell(new \Tk\Table\Cell\Date('dateStart'));
        $this->appendCell(new \Tk\Table\Cell\Date('dateEnd'));

        $this->appendCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->appendCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->appendCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->appendFilter(new \Tk\Form\Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::create('New Subject', 'fa fa-plus', \Uni\Uri::createHomeUrl('/subjectEdit.html')));
        $this->appendAction(\Tk\Table\Action\Csv::create());
        $this->appendAction(\Tk\Table\Action\Delete::create());

        return $this;
    }

    /**
     * @param array $filter
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\Subject[]
     * @throws \Exception
     */
    public function findList($filter = array())
    {
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Uni\Db\SubjectMap::create()->findFiltered($filter, $this->getTool());
        return $list;
    }

}