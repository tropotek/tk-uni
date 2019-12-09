<?php
namespace Uni\Table;


use Exception;
use Tk\Db\Map\ArrayObject;
use Tk\Db\Tool;
use Tk\Form\Field\Input;
use Tk\Table\Action\Csv;
use Tk\Table\Action\Delete;
use Tk\Table\Cell\Boolean;
use Tk\Table\Cell\Checkbox;
use Tk\Table\Cell\Date;
use Tk\Table\Cell\Email;
use Tk\Table\Cell\Text;
use Uni\TableIface;

/**
 * @author Mick Mifsud
 * @created 2018-07-24
 * @link http://tropotek.com.au/
 * @license Copyright 2018 Tropotek
 */
class Subject extends TableIface
{


    /**
     * @return $this
     * @throws Exception
     */
    public function init()
    {

        $this->appendCell(new Checkbox('id'));
        $this->appendCell(new Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Text('code'));
        $this->appendCell(new Text('courseId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            $course = \Uni\Config::getInstance()->getCourseMapper()->find($value);
            if ($course)
                $value = $course->getName();
            return $value;
        });
        $this->appendCell(new Email('email'));
        $this->appendCell(Date::createDate('dateStart', \Tk\Date::FORMAT_ISO_DATE));
        $this->appendCell(Date::createDate('dateEnd', \Tk\Date::FORMAT_ISO_DATE));

        $this->appendCell(new Boolean('notify'));
        $this->appendCell(new Boolean('publish'));
        $this->appendCell(new Boolean('active'));
        $this->appendCell(new Date('created'));

        // Filters
        $this->appendFilter(new Input('keywords'))->setLabel('')->setAttr('placeholder', 'Search');

        // Actions
        $this->appendAction(Delete::create());
        $this->appendAction(Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|Tool $tool
     * @return ArrayObject|\Uni\Db\Subject[]
     * @throws Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool('dateStart DESC');
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = $this->getConfig()->getSubjectMapper()->findFiltered($filter, $tool);
        return $list;
    }

}