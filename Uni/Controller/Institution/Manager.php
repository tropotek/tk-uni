<?php
namespace Uni\Controller\Institution;


/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Uni\Controller\AdminManagerIface
{


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->getConfig()->getCrumbs()->reset();
    }


    /**
     *
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->setPageTitle('Institution Manager');

        $this->table = \Uni\Table\Institution::create()->init();
        $this->getTable()->setList($this->table->findList());

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->getActionPanel()->add(\Tk\Ui\Button::create('New Institution',
            \Uni\Uri::createHomeUrl('/institutionEdit.html'), 'fa fa-university'));

        $template = parent::show();

        $template->appendTemplate('table', $this->getTable()->show());

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
<div class="">

  <div class="tk-panel" data-panel-icon="fa fa-university" var="table"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}



