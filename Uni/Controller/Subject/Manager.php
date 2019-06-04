<?php
namespace Uni\Controller\Subject;

use Dom\Loader;
use Dom\Template;
use Exception;
use Tk\Request;
use Tk\Table;
use Tk\Ui\Link;
use Uni\Controller\AdminManagerIface;
use Uni\Table\Institution;
use Uni\Uri;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends AdminManagerIface
{

    /**
     * @var Table
     */
    protected $table = null;



    /**
     * @throws Exception
     */
    public function __construct()
    {
        //$this->getConfig()->getCrumbs()->reset();
    }

    /**
     *
     * @param Request $request
     * @throws Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Subject Manager');

        $this->table = Institution::create()->init();

        $filter = array();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        if ($this->getUser()->isStudent() || $this->getUser()->isStaff())
            $filter['userId'] = $this->getUser()->getId();

        $this->getTable()->setList($this->table->findList($filter));

    }

    /**
     * @return Template
     */
    public function show()
    {
        $this->getActionPanel()->append(Link::createBtn('New Subject',
            Uri::createHomeUrl('/subjectEdit.html'), 'fa fa-graduation-cap'));

        $template = parent::show();

        $template->appendTemplate('panel', $this->getTable()->show());

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

  <div class="tk-panel" data-panel-title="Subject" data-panel-icon="fa fa-graduation-cap" var="panel"></div>
  
</div>
HTML;

        return Loader::load($xhtml);
    }


}