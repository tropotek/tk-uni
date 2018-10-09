<?php
namespace Uni\Controller\Role;

use Dom\Template;
use Tk\Form\Field;
use Tk\Request;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Role Manager');

    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->setPageTitle('Staff Role Manager');


        $this->table = \Uni\Config::getInstance()->createTable('role-manager');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Uni\Uri::createHomeUrl('/roleEdit.html'));
        //$this->table->appendCell(new \Tk\Table\Cell\Text('type'));
        $this->table->appendCell(\Tk\Table\Cell\Text::create('description')->setCharacterLimit(100));
        $this->table->appendCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->appendCell(new \Tk\Table\Cell\Boolean('static'));
        $this->table->appendCell(\Tk\Table\Cell\Date::create('created')->setFormat(\Tk\Date::FORMAT_ISO_DATE));

        // Filters
        $this->table->appendFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        $list = array(
            '-- Type --' => '',
            'Staff' => \Uni\Db\Role::TYPE_COORDINATOR
            //,'Student' => \Uni\Db\Role::TYPE_STUDENT
        );
        //$this->table->appendFilter(Field\Select::createSelect('type', $list));


        // Actions
        $this->table->appendAction(\Tk\Table\Action\Delete::create()->setOnDelete(function (\Tk\Table\Action\Delete $action, $obj) {
            /** @var \Uni\Db\Role $obj */
            if ($obj->isStatic()) {
                \Tk\Alert::addWarning('Cannot delete system static roles.');
                return false;
            }
        }));
        $this->table->appendAction(\Tk\Table\Action\Csv::create());

        $filter = $this->table->getFilterValues();
        if (empty($filter['type'])) {
            $filter['type'] = array(
                \Uni\Db\Role::TYPE_COORDINATOR
                //,\Uni\Db\Role::TYPE_STUDENT
            );
        }
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();

        $list = $this->getConfig()->getRoleMapper()->findFiltered($filter, $this->table->getTool());
        $this->table->setList($list);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $btn = \Tk\Ui\ButtonDropdown::createButtonDropdown('Add Role', 'fa fa-id-badge', array(
            \Tk\Ui\Link::create('Staff Role', \Uni\Uri::createHomeUrl('/roleEdit.html')->set('type', \Uni\Db\Role::TYPE_COORDINATOR))
            //,\Tk\Ui\Link::create('Student Role', \Uni\Uri::createHomeUrl('/roleEdit.html')->set('type', \Uni\Db\Role::TYPE_STUDENT))
        ));
        $this->getActionPanel()->add($btn);

        $template = parent::show();

        $template->appendTemplate('table', $this->table->getRenderer()->show());

        return $template;
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="tk-panel" data-panel-title="Role" data-panel-icon="fa fa-id-badge" var="table"></div>
  <!--<div class="panel panel-default">-->
    <!--<div class="panel-heading"><i class="fa fa-id-badge fa-fw"></i> Role</div>-->
    <!--<div class="panel-body">-->
      <!--<div var="table"></div>-->
    <!--</div>-->
  <!--</div>-->
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}