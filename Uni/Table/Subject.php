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
     * @param string $tableId
     */
    public function __construct($tableId = 'student-table')
    {
        parent::__construct($tableId);
    }

    /**
     * @return \$this
     * @throws \Exception
     */
    public function init()
    {

        $this->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key');
        $this->addCell(new \Tk\Table\Cell\Text('code'));
        //$this->addCell(new \Tk\Table\Cell\Email('email'));
        //$this->addCell(new \Tk\Table\Cell\Date('dateStart'));
        $this->addCell(new \Tk\Table\Cell\Date('dateEnd'));

        $this->addCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->addCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->addFilter(new \Tk\Form\Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Search');

        // Actions
        //$this->addAction(\Tk\Table\Action\Link::create('New Subject', 'fa fa-plus', \Uni\Uri::createHomeUrl('/subjectEdit.html')));
        $this->addAction(\Tk\Table\Action\Csv::create());
        $this->addAction(\Tk\Table\Action\Delete::create());

        return $this;
    }

    /**
     * @param array $filter
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Mentor[]
     * @throws \Exception
     */
    public function findList($filter = array())
    {
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Uni\Db\SubjectMap::create()->findFiltered($filter, $this->getTool());
        return $list;
    }

}