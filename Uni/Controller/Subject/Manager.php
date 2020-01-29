<?php
namespace Uni\Controller\Subject;

use Dom\Loader;
use Dom\Template;
use Exception;
use Tk\Request;
use Tk\Ui\Link;
use Uni\Controller\AdminManagerIface;
use Uni\Table\Subject;
use Uni\Uri;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends AdminManagerIface
{

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Subject Manager');
        //$this->getConfig()->getCrumbs()->reset();
    }

    /**
     *
     * @param Request $request
     * @throws Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(Subject::create()->setEditUrl(\Uni\Uri::createHomeUrl('/subjectEdit.html'))->init());

        $filter = array();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        $filter['courseId'] = $request->get('courseId', 0);
        if ($this->getAuthUser()->isStudent())
            $filter['userId'] = $this->getAuthUser()->getId();

        $this->getTable()->setList($this->getTable()->findList($filter));

    }

    public function initActionPanel()
    {
        $this->getActionPanel()->append(Link::createBtn('New Subject',
            Uri::createHomeUrl('/subjectEdit.html'), 'fa fa-graduation-cap'));
    }

    /**
     * @return Template
     */
    public function show()
    {
        $this->initActionPanel();
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

  <div class="tk-panel" data-panel-title="Subject Manager" data-panel-icon="fa fa-graduation-cap" var="panel"></div>
  
</div>
HTML;

        return Loader::load($xhtml);
    }


}