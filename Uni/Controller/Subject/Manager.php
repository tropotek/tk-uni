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

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Uni\Uri::createHomeUrl('/subjectEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        $this->table->addCell(new \Tk\Table\Cell\Email('email'));
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'))->setOrderProperty()->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\Subject $obj */
            return $obj->isActive();
        });
        $this->table->addCell(\Tk\Table\Cell\Date::create('dateStart')->setFormat(\Tk\Date::FORMAT_ISO_DATE));
        $this->table->addCell(\Tk\Table\Cell\Date::create('dateEnd')->setFormat(\Tk\Date::FORMAT_ISO_DATE));
        $this->table->addCell(\Tk\Table\Cell\Date::create('created')->setFormat(\Tk\Date::FORMAT_ISO_DATE));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');
        if ($this->getUser()->isStaff()) {
            $list = array('-- Show All --' => '', 'My Subjects' => '1');
            $this->table->addFilter(new Field\Select('userId', $list))->setLabel('')->setValue('1');
        }

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New Subject', 'fa fa-plus', \Tk\Uri::create('/client/subjectEdit.html')));
        $this->table->addAction(\Tk\Table\Action\Delete::create());
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        if (!empty($filter['userId'])) {
            $filter['userId'] = $this->getUser()->id;
        }
        
        $users = $this->getConfig()->getSubjectMapper()->findFiltered($filter, $this->table->getTool('a.id'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->table->getRenderer()->show());

        $this->getActionPanel()->add(\Tk\Ui\Button::create('New Subject', \Uni\Uri::createHomeUrl('/subjectEdit.html'), 'fa fa-graduation-cap'));

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

  <div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-graduation-cap fa-fw"></i> Subject</div>
    <div class="panel-body">
      <div var="table"></div>
    </div>
  </div>
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}