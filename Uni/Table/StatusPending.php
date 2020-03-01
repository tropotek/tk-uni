<?php
namespace Uni\Table;

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
class StatusPending extends \App\TableIface
{

    /**
     * Supervisor constructor.
     * @param string $tableId
     */
    public function __construct($tableId = '')
    {
        parent::__construct($tableId);
        $this->setRenderer(\Tk\Table\Renderer\Dom\Div::create($this));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->addCss('status-table');

        $this->appendCell(new \Tk\Table\Cell\Text('id'))->setLabel('ID')->addOnCellHtml(function ($cell, $obj, $html) {
            /** @var \Tk\Table\Cell\Iface $cell */
            /** @var \Uni\Db\Status $obj */
            $strat = $obj->getModelStrategy();
            if ($strat && $obj->getModel()) {
                $cell->setAttr('title', $strat->getLabel());
                return $strat->getPendingIcon();
            }
            return '';
        });

        $this->appendCell(new \Tk\Table\Cell\Text('name'))->addOnCellHtml(function ($cell, $obj, $html) {
            /** @var \Tk\Table\Cell\Iface $cell */
            /** @var \Uni\Db\Status $obj */
            $strat = $obj->getModelStrategy();
            $cell->removeAttr('title');
            if ($strat && $obj->getModel()) {
                return $strat->getPendingHtml();
            }
            return '';
        });

        // Actions
        $this->appendCell($this->getActionCell());

        $this->appendCell(\Tk\Table\Cell\Date::createDate('created'))->addOnCellHtml(function ($cell, $obj, $html) {
            /** @var \Tk\Table\Cell\Iface $cell */
            /** @var \Uni\Db\Status $obj */
            $cell->removeAttr('title');
            return sprintf('<div class="status-created">%s</div>', $obj->getCreated(\Tk\Date::FORMAT_MED_DATE));
        });

        // Filters
        $filter = array();
        $filter['courseId'] = $this->getConfig()->getCourseId();
        $list = \Uni\Db\StatusMap::create()->findKeys($filter);
        $this->appendFilter(new \Tk\Form\Field\Select('fkey', $list))->prependOption('-- Type --', '')->setAttr('placeholder', 'Keywords');

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
        $list = \Uni\Db\StatusMap::create()->findCurrentStatus($filter, $tool);
        return $list;
    }

}