<?php
namespace Uni\Controller\Institution;

use Tk\Request;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Listing extends \Uni\Controller\AdminIface
{
    /**
     * @var null|\Tk\Table
     */
    protected $table = null;

    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Institutions');
        $this->getActionPanel()->setVisible(false);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->table = $this->getConfig()->createTable('institution-list');
        $this->table = $this->getConfig()->createTable('institution-list');
        $this->table->setRenderer($this->getConfig()->createTableRenderer($this->table));

        $actionsCell = new \Tk\Table\Cell\Actions();
        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Login', \Tk\Uri::create(), 'fa  fa-sign-in', 'button-small soft')->setAttr('title', 'Institution Login'))
            ->addOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                $button->setUrl($obj->getLoginUrl());
            });

        $this->table->appendCell($actionsCell);
        $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->setUrl(\Tk\Uri::create('/institutionEdit.html'))
            ->addOnPropertyValue(function ($cell, $obj, $value) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $cell \Tk\Table\Cell\Text */
                $cell->setUrl($obj->getLoginUrl());
                return $value;
            });
        $this->table->appendCell(new \Tk\Table\Cell\Text('description'))->addCss('key')->setCharacterLimit(150);

        $filter = $this->table->getFilterValues();
        $filter['active'] = true;
        $list = $this->getConfig()->getInstitutionMapper()->findFiltered($filter, $this->table->getTool());
        $this->table->setList($list);

    }

    /**
     * show()
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::getTemplate();

        $template->appendTemplate('table', $this->table->getRenderer()->show());


        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="container institution-list">
  <h2 class="title">Institutions</h2>
  
  <p>Select an Institution you would like to login to.</p>
  <div var="table"></div>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
    
  
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}