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
     * @var \Tk\Table\Cell\Actions
     */
    protected $actionsCell = null;

    /**
     * Manager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->actionsCell = new \Tk\Table\Cell\Actions();
        $this->getConfig()->getCrumbs()->reset();
    }

    /**
     * @return \Tk\Table\Cell\Actions
     */
    public function getActionsCell()
    {
        return $this->actionsCell;
    }


    /**
     *
     * @param Request $request
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     * @throws \Tk\Form\Exception
     */
    public function doDefault(Request $request)
    {
        $this->setPageTitle('Institution Manager');


        $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
            ->setOnShow(function ($cell, $obj, $button) {
                /* @var $obj \Uni\Db\Institution */
                /* @var $button \Tk\Table\Cell\ActionButton */
                if (\Uni\Listener\MasqueradeHandler::canMasqueradeAs(\Uni\Config::getInstance()->getUser(), $obj->getUser())) {
                    $button->setUrl(\Uni\Uri::create()->set(\Uni\Listener\MasqueradeHandler::MSQ, $obj->getUser()->getHash()));
                }
            });
            
        $this->table = \Uni\Config::getInstance()->createTable('InstitutionList');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(\Tk\Uri::create('admin/institutionEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('userId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\Institution $obj */
            $user = $obj->getUser();
            if ($user) {
                return $user->getName();
            }
            return $value;
        });
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('description'))->setCharacterLimit(64);
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->addAction(\Tk\Table\Action\Delete::create());
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $users = \Uni\Db\InstitutionMap::create()->findFiltered($this->table->getFilterValues(), $this->table->getTool('a.id'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();
        $template->replaceTemplate('table', $this->table->getRenderer()->show());

        $this->getActionPanel()->add(\Tk\Ui\Button::create('New Institution',
            \Uni\Uri::createHomeUrl('/institutionEdit.html'), 'fa fa-university'));

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
    <div class="panel-heading">
      <i class="fa fa-university fa-fw"></i> Institution
    </div>
    <div class="panel-body">
      <div var="table"></div>
    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}



