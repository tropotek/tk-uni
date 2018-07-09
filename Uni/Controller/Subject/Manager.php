<?php
namespace Uni\Controller\Subject;

use Dom\Template;
use Tk\Form\Field;
use Tk\Request;


/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Bs\Controller\AdminIface
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
     * @throws \Tk\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Form\Exception
     * @throws \Tk\Form\Exception
     * @throws \Tk\Exception
     */
    public function doDefault(Request $request)
    {
        $this->institution = $this->getUser()->getInstitution();
        if (!$this->institution)
            throw new \Tk\Exception('Institution Not Found.');

        $this->table = \Uni\Config::getInstance()->createTable('SubjectList');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Uni\Uri::createHomeUrl('/subjectEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Date('dateStart'));
        $this->table->addCell(new \Tk\Table\Cell\Date('dateEnd'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');
        if ($this->getUser()->hasRole(\Uni\Db\User::ROLE_STAFF)) {
            $list = array('-- Show All --' => '', 'My Subjects' => '1');
            $this->table->addFilter(new Field\Select('userId', $list))->setLabel('')->setValue('1');
        }

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New Subject', 'fa fa-plus', \Tk\Uri::create('/client/subjectEdit.html')));
        $this->table->addAction(\Tk\Table\Action\Delete::create());
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institution->id;       // <------- ??????? For new institution still shows other subjects????
        if (!empty($filter['userId'])) {
            $filter['userId'] = $this->getUser()->id;
        }
        
        $users = \Uni\Db\SubjectMap::create()->findFiltered($filter, $this->table->getTool('a.id'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->replaceTemplate('table', $this->table->getRenderer()->show());

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