<?php
namespace Uni\Controller\Subject;

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
        $this->setPageTitle('Subject Manager');
    }

    /**
     *
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {

        $this->table = \Uni\Config::getInstance()->createTable('subject-manager');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Uni\Uri::createHomeUrl('/subjectEdit.html'));
        $this->table->appendCell(new \Tk\Table\Cell\Text('code'));
        $this->table->appendCell(new \Tk\Table\Cell\Email('email'));
        $this->table->appendCell(new \Tk\Table\Cell\Boolean('notify'));
        $this->table->appendCell(new \Tk\Table\Cell\Boolean('publish'));
        $this->table->appendCell(new \Tk\Table\Cell\Boolean('active'))->setOrderProperty()->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\Subject $obj */
            return $obj->isActive();
        });
        $this->table->appendCell(\Tk\Table\Cell\Date::create('dateStart')->setFormat(\Tk\Date::FORMAT_ISO_DATE));
        $this->table->appendCell(\Tk\Table\Cell\Date::create('dateEnd')->setFormat(\Tk\Date::FORMAT_ISO_DATE));
        //$this->table->appendCell(\Tk\Table\Cell\Date::create('created')->setFormat(\Tk\Date::FORMAT_ISO_DATE));

        // Filters
        $this->table->appendFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');
        if ($this->getUser()->isStaff()) {
            $list = array('-- Show All --' => '', 'My Subjects' => '1');
            $this->table->appendFilter(new Field\Select('userId', $list))->setLabel('')->setValue('1');
        }

        // Actions
        //$this->table->appendAction(\Tk\Table\Action\Button::getInstance('New Subject', 'fa fa-plus', \Tk\Uri::create('/client/subjectEdit.html')));
        $this->table->appendAction(\Tk\Table\Action\Delete::create());
        $this->table->appendAction(\Tk\Table\Action\Csv::create());

        $this->setList();
    }

    /**
     * @throws \Exception
     */
    public function setList()
    {
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        if (!empty($filter['userId'])) {
            $filter['userId'] = $this->getUser()->id;
        }

        $users = $this->getConfig()->getSubjectMapper()->findFiltered($filter, $this->table->getTool('dateStart DESC'));
        $this->table->setList($users);
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->table->getRenderer()->show());

        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Subject', \Uni\Uri::createHomeUrl('/subjectEdit.html'), 'fa fa-graduation-cap'));

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

  <div class="tk-panel" data-panel-title="Subject" data-panel-icon="fa fa-graduation-cap" var="table"></div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}