<?php
namespace Uni\Controller\User;

use Dom\Template;
use Tk\Form\Field;

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
     * @var \Uni\Db\Subject
     */
    protected $subject = null;

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;

    /**
     * @var \Tk\Table\Cell\Actions
     */
    protected $actionsCell = null;

    /**
     * Setup the controller to work with users of this role
     * @var string
     */
    protected $targetRole = 'user';


    /**
     * Manager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('User Manager');
        $this->actionsCell = new \Tk\Table\Cell\Actions();
    }

    /**
     * @param \Tk\Request $request
     * @param string $targetRole
     * @throws \Exception
     */
    public function doDefaultRole(\Tk\Request $request, $targetRole)
    {
        $this->targetRole = $targetRole;
        switch($targetRole) {
            case \Uni\Db\Role::TYPE_ADMIN:
                $this->setPageTitle('Admin Users');
                break;
            case \Uni\Db\Role::TYPE_STAFF:
                $this->setPageTitle('Staff Manager');
                break;
            case \Uni\Db\Role::TYPE_STUDENT:
                $this->setPageTitle('Student Manager');
                break;
        }
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @param string $subjectCode
     * @param string $targetRole
     * @throws \Exception
     */
    public function doSubject(\Tk\Request $request, $subjectCode, $targetRole)
    {
        $this->targetRole = $targetRole;
        $this->subject = $this->getConfig()->getSubjectMapper()->findByCode($subjectCode, $this->getConfig()->getInstitutionId());
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        if (!$this->subject && $request->has('subjectId'))
            $this->subject = $this->getConfig()->getSubjectMapper()->find($request->get('subjectId'));
        if (!$this->editUrl) {
            $this->editUrl = \Uni\Uri::createHomeUrl('/'.$this->targetRole.'Edit.html');
            if ($this->getConfig()->isSubjectUrl()) {
                $this->editUrl = \Uni\Uri::createSubjectUrl('/'.$this->targetRole.'Edit.html');
            }
        }


        if (!$this->getUser()->isStudent()) {
            $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade',
                \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
                ->setOnShow(function ($cell, $obj, $button) {
                    /* @var $obj \Uni\Db\User */
                    /* @var $button \Tk\Table\Cell\ActionButton */
                    if (\Uni\Listener\MasqueradeHandler::canMasqueradeAs(\Uni\Config::getInstance()->getUser(), $obj)) {
                        $button->setUrl(\Uni\Uri::create()->set(\Uni\Listener\MasqueradeHandler::MSQ, $obj->getHash()));
                    } else {
                        $button->setAttr('disabled', 'disabled')->addCss('disabled');
                        //$button->setVisible(false);
                    }
                });
        }

        $this->table = \Uni\Config::getInstance()->createTable($this->targetRole . '-user-list');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl(clone $this->editUrl);
        $this->table->addCell(new \Tk\Table\Cell\Text('username'));
        $this->table->addCell(new \Tk\Table\Cell\Email('email'));
        $this->table->addCell(new \Tk\Table\Cell\Text('roleId'))->setOnPropertyValue(function ($cell, $obj, $value) {
            /** @var \Uni\Db\User $obj */
            if ($obj->getRole())
                $value = $obj->getRole()->getName();
            return $value;
        });
        $this->table->addCell(new \Tk\Table\Cell\Text('uid'))->setLabel('UID');
        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(new \Tk\Table\Cell\Date('lastLogin'));
        $this->table->addCell(\Tk\Table\Cell\Date::create('created')->setFormat(\Tk\Date::FORMAT_ISO_DATE));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->addAction(\Tk\Table\Action\Delete::create()->setExcludeIdList(array(1)));
        $this->table->addAction(\Tk\Table\Action\Csv::create());

        $this->initTable();

        $this->initActionPanel($this->getActionPanel());
    }

    /**
     * @throws \Exception
     */
    public function initTable()
    {
        $filter = $this->table->getFilterValues();
        if ($this->getUser()->getInstitution())
            $filter['institutionId'] = $this->getUser()->getInstitution()->id;

        if (empty($filter['type'])) {
            $filter['type'] = $this->targetRole;
        }
        $users = $this->getConfig()->getUserMapper()->findFiltered($filter, $this->table->getTool('a.name'));
        $this->table->setList($users);
    }

    /**
     * @param \Tk\Ui\Admin\ActionPanel $actionPanel
     */
    protected function initActionPanel($actionPanel)
    {
        $actionPanel->add(\Tk\Ui\Button::create('New ' . ucfirst($this->targetRole), clone $this->editUrl, 'fa fa-user-plus'));

        


    }

    /**
     * @return \Tk\Table\Cell\Actions
     */
    public function getActionsCell()
    {
        return $this->actionsCell;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
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
<div>

  <div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-users fa-fw"></i> <span var="panelTitle"></span></div>
    <div class="panel-body">
      <div var="table"></div>
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}