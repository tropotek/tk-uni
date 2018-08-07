<?php
namespace Uni\Ui\Table;

use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @deprecated Use \Uni\Table\User
 */
class User extends \Dom\Renderer\Renderer
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var int
     */
    protected $institutionId = 0;

    /**
     * @var int
     */
    protected $subjectId = 0;

    /**
     * @var null|array|string
     */
    protected $roleType = null;

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;

    /**
     * @var \Tk\Table\Cell\Actions
     */
    protected $actionsCell = null;


    /**
     * @param int $institutionId
     * @param null|array|string $roleType
     * @param int $subjectId
     * @param null|\Tk\Uri $editUrl
     * @throws \Exception
     */
    public function __construct($institutionId = 0, $roleType = null, $subjectId = 0, $editUrl = null)
    {
        $this->institutionId = $institutionId;
        //$this->roleType = $roleType;
        $this->subjectId = $subjectId;
        $this->editUrl = $editUrl;
        $this->doDefault();
    }


    /**
     * @return \Dom\Template|Template|string
     * @throws \Exception
     */
    public function doDefault()
    {
        $this->table = new \Tk\Table('subject-user-list');
        $this->table->setRenderer(\Tk\Table\Renderer\Dom\Table::create($this->table));

        $this->actionsCell = new \Tk\Table\Cell\Actions();
        $this->actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Masquerade', \Tk\Uri::create(), 'fa  fa-user-secret', 'tk-masquerade'))
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
        $this->table->addCell($this->actionsCell);
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setUrl($this->editUrl);

//        if ($this->institutionId) {
//            $this->table->addCell(new \Tk\Table\Cell\Text('subject'))
//                ->setOnPropertyValue(function ($csll, $obj, $value) {
//                    $list = \Uni\Config::getInstance()->getSubjectMapper()->findByUserId($obj->id, $this->institutionId, \Tk\Db\Tool::create('a.name'));
//                    $val = '';
//                    /** @var \Uni\Db\Subject $subject */
//                    foreach ($list as $subject) {
//                        $val .= $subject->code . ', ';
//                    }
//                    if ($val)
//                        $val = rtrim($val, ', ');
//                    return $val;
//                });
//        }
        $this->table->addCell(new \Tk\Table\Cell\Email('email'));

        if (!$this->roleType) {
            $this->table->addCell(new \Tk\Table\Cell\Text('roleId'))->setOnPropertyValue(function ($cell, $obj, $value) {
                /** @var \Uni\Db\User $obj */
                if ($obj->getRole())
                    $value = $obj->getRole()->getName();
                return $value;
            });
        }

        $this->table->addCell(new \Tk\Table\Cell\Boolean('active'));
        $this->table->addCell(\Tk\Table\Cell\Date::create('created')->setFormat(\Tk\Date::FORMAT_ISO_DATE));

        // Filters
        //$this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Search');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Csv::create());

        // Set list
        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institutionId;
        $filter['subjectId'] = $this->subjectId;
        $filter['type'] = $this->roleType;

        $users = \Uni\Config::getInstance()->getUserMapper()->findFiltered($filter, $this->table->getTool('a.name'));
        $this->table->setList($users);

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->table->getRenderer()->show();
        $this->setTemplate($this->table->getRenderer()->getTemplate());
        return $this->table->getRenderer()->getTemplate();
    }

}
