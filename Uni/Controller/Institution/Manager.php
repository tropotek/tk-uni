<?php
namespace Uni\Controller\Institution;

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
class Manager extends \Uni\Controller\AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->getConfig()->getCrumbs()->reset();
    }


    /**
     *
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Institution Manager');

        $this->table = \Uni\Table\Institution::create()->init();
        $this->table->setList($this->table->findList());

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->getActionPanel()->add(\Tk\Ui\Button::create('New Institution',
            \Uni\Uri::createHomeUrl('/institutionEdit.html'), 'fa fa-university'));

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

  <div class="tk-panel" data-panel-title="Institution" data-panel-icon="fa fa-university" var="table"></div>
  <!--<div class="panel panel-default">-->
    <!--<div class="panel-heading">-->
      <!--<i class="fa fa-university fa-fw"></i> Institution-->
    <!--</div>-->
    <!--<div class="panel-body">-->
      <!--<div var="table"></div>-->
    <!--</div>-->
  <!--</div>-->

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}



