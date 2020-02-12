<?php
namespace Uni\Table;

use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Status::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-05-23
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Status extends \App\TableIface
{
    /**
     * @var bool
     */
    protected $showLogUrl = true;

    /**
     * @var array
     */
    protected $selectedColumns = array('name', 'userId', 'message');


    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
    
        $this->appendCell(new Cell\Checkbox('id'))->setLabel('ID');
        $this->appendCell(new Cell\Text('name'))->setUrl($this->getEditUrl());

//        $logUrl = null;
//        if ($this->isShowLogUrl())
//            $logUrl = \Uni\Uri::createSubjectUrl('/mailLogManager.html');


        $this->appendCell(new \Tk\Table\Cell\Text('event'));
//            ->setOnPropertyValue(function ($cell, $obj, $value) {
//                /** @var $cell \Tk\Table\Cell\Text */
//                /** @var $obj \Uni\Db\Status */
//
//                return $value;
//            });
//            ->setOnCellHtml(function ($cell, $obj, $html) {
//                /** @var $cell \Tk\Table\Cell\Text */
//                /** @var $obj \Uni\Db\Status */
//                $value = $propValue = $cell->getPropertyValue($obj);
//                if ($cell->getCharLimit() && strlen($propValue) > $cell->getCharLimit()) {
//                    $propValue = substr($propValue, 0, $cell->getCharLimit()-3) . '...';
//                }
//                $cell->setAttr('title', $value);
//                $html = htmlentities($propValue);
//
////                $url = $cell->getCellUrl($obj);
////                if (!$url && $obj->getEvent()) {
////                    $logList = \App\Db\MailLogMap::create()->findFiltered(array('statusId' => $obj->getId()));
////                    if ($logList->count() && $logUrl) {
////                        $cell->setAttr('title', 'Click to view all email logs for this status change.');
////                        $url = $logUrl->set('statusId', $obj->getId());
////                    }
////                }
//
//                if ($url) {
//                    $html = sprintf('<a href="%s">%s</a>', htmlentities($url->toString()), htmlentities($propValue));
//                }
//                return $html;
//            });

        $this->appendCell(new Cell\Text('userId'))->setOnPropertyValue(function ($cell, $obj, $value) {
                /** @var $obj \Uni\Db\Status */
                $value = '';
                if ($obj->getUser())
                    $value = $obj->getUser()->getName();
                return $value;
            });

        $this->appendCell(new Cell\Text('message'))->addCss('key wrap-normal')
            ->setOnCellHtml(function ($cell, $obj, $html) {
                /** @var $cell \Tk\Table\Cell\Text */
                /** @var $obj \Uni\Db\Status */
                $cell->setAttr('title', 'Message');
                return '<small>' . nl2br(\Tk\Str::stripEntities($html)) . '</small>' ;
            });
        $this->appendCell(new Cell\Date('created'));

        // Filters
        //$this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::create('New Status', 'fa fa-plus', \Bs\Uri::createHomeUrl('/statusEdit.html')));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setSelected($this->selectedColumns));
        //$this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());
        
        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Uni\Db\Status[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool('created DESC');
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Uni\Db\StatusMap::create()->findFiltered($filter, $tool);
        return $list;
    }

    /**
     * Note only use this if you need to modify the columns before the init() method
     * @return array
     */
    public function getSelectedColumns()
    {
        return $this->selectedColumns;
    }

    /**
     * Note only use this if you need to set the columns before the init() method
     *
     * @param array $selectedColumns
     * @return Status
     */
    public function setSelectedColumns($selectedColumns)
    {
        $this->selectedColumns = $selectedColumns;
        return $this;
    }


}