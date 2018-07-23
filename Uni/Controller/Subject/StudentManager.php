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
class StudentManager extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \Uni\Db\Institution
     */
    private $institution = null;


    /**
     * StudentManager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('My Subjects');
        $this->getConfig()->unsetSubject();
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->institution = $this->getUser()->getInstitution();
        if (!$this->institution)
            throw new \Tk\Exception('Institution Not Found.');

        $this->table = \Uni\Config::getInstance()->createTable('SubjectList');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Uni\Uri::createSubjectUrl('/index.html'))
            ->setOnPropertyValue(function ($cell, $obj, $value) {
                $cell->setUrl(\Uni\Uri::createSubjectUrl('/index.html', $obj));
                return $value;
            });
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        $this->table->addCell(new \Tk\Table\Cell\Email('email'));
        $this->table->addCell(new \Tk\Table\Cell\Date('dateStart'));
        $this->table->addCell(new \Tk\Table\Cell\Date('dateEnd'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institution->id;       // <------- ??????? For new institution still shows other subjects????
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

        $template->replaceTemplate('table', $this->table->getRenderer()->show());

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