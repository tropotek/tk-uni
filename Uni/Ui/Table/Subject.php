<?php
namespace Uni\Ui\Table;

use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @deprecated Use \Uni\Table\Subject
 */
class Subject extends \Dom\Renderer\Renderer
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
     * @var \Tk\Uri|callable
     */
    protected $editUrl = null;

    /**
     * @var null|\Uni\Db\User
     */
    protected $user = null;


    /**
     *  constructor.
     *
     * @param int $institutionId
     * @param \Tk\Uri|callable|null $editUrl
     * @param null|\Uni\Db\User $user
     * @throws \Exception
     */
    public function __construct($institutionId = 0, $editUrl = null, $user = null)
    {
        $this->institutionId = $institutionId;
        $this->editUrl = $editUrl;
        $this->user = $user;
        $this->doDefault();
    }


    /**
     * @return \Dom\Template|Template|string
     * @throws \Exception
     */
    public function doDefault()
    {
        $this->table = new \Tk\Table('SubjectList');
        $this->table->setRenderer(\Tk\Table\Renderer\Dom\Table::create($this->table));

        //$this->table->appendCell(new \Tk\Table\Cell\Checkbox('id'));
        $c = $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key');
        if (is_callable($this->editUrl)) {
            $c->setOnPropertyValue($this->editUrl);
        } else {
            $c->setUrl($this->editUrl);
        }
        $this->table->appendCell(new \Tk\Table\Cell\Text('code'));
        //$this->table->appendCell(new \Tk\Table\Cell\Text('email'));
        //$this->table->appendCell(new \Tk\Table\Cell\Date('dateStart'));
        $this->table->appendCell(new \Tk\Table\Cell\Date('dateEnd'));

        $this->table->appendCell(new \Tk\Table\Cell\Boolean('active'));
        //$this->table->appendCell(new \Tk\Table\Cell\Date('created'))->setFormat(\Tk\Table\Cell\Date::FORMAT_RELATIVE);
        $this->table->appendCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->appendFilter(new \Tk\Form\Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        $this->table->appendAction(\Tk\Table\Action\Link::create('New Subject', 'fa fa-plus', \Uni\Uri::createHomeUrl('/subjectEdit.html')));
        $this->table->appendAction(\Tk\Table\Action\Csv::create());

        // Set list
        $filter = $this->table->getFilterValues();
        if ($this->institutionId)
            $filter['institutionId'] = $this->institutionId;
        if ($this->user)
            $filter['userId'] = $this->user->getId();
        $users = \Uni\Config::getInstance()->getSubjectMapper()->findFiltered($filter, $this->table->getTool('a.id'));
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