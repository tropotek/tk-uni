<?php
namespace Uni\Controller\User;


use Uni\Db\User;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends \Uni\Controller\AdminManagerIface
{

    /**
     * @var null|\Tk\Uri
     */
    protected $editUrl = null;

    /**
     * Setup the controller to work with users of this type
     * @var string
     */
    protected $targetType = '';


    /**
     * Manager constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('User Manager');

        if ($this->getAuthUser()->isClient()) {
            $this->getConfig()->resetCrumbs();
        }
    }

    /**
     * @return string
     */
    public function getTargetType(): string
    {
        return $this->targetType;
    }

    /**
     * @param \Tk\Request $request
     * @param string $targetType
     * @throws \Exception
     */
    public function doDefaultType(\Tk\Request $request, $targetType)
    {
        $this->targetType = $targetType;
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        switch($this->getTargetType()) {
            case \Uni\Db\User::TYPE_ADMIN:
                $this->setPageTitle('Admin Users');
                break;
            case \Uni\Db\User::TYPE_STAFF:
                $this->setPageTitle('Staff Manager');
                break;
            case \Uni\Db\User::TYPE_STUDENT:
                $this->setPageTitle('Student Manager');
                break;
        }

        if (!$this->editUrl) {
            $this->editUrl = \Uni\Uri::createHomeUrl('/'.$this->getTargetType().'UserEdit.html');
            if ($this->getConfig()->isSubjectUrl()) {
                $this->editUrl = \Uni\Uri::createSubjectUrl('/'.$this->getTargetType().'UserEdit.html');
            }
        }

        $this->setTable(\Uni\Table\User::create()->setEditUrl($this->editUrl));
        if (!$this->getAuthUser()->isStudent())
            $this->getTable()->getActionCell()->removeButton('Masquerade');
        $this->getTable()->init();


        if ($this->getTargetType() == User::TYPE_STAFF) {
            $this->getTable()->appendCell(new \Tk\Table\Cell\Text('role'), 'uid')
                ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, $obj, $value) {
                    /** @var $obj \Uni\Db\User */
                    $value = '';
                    if ($obj->isCoordinator()) {
                        $value .= 'Coordinator, ';
                    }
                    if ($obj->isMentor()) {
                        $value .= 'Mentor, ';
                    }
                    if ($obj->isLecturer()) {
                        $value .= 'Lecturer, ';
                    }
                    if (!$value) {
                        $value = 'Staff';
                    }
                    return trim($value, ', ');
                });
        } else {
            $this->getTable()->appendCell(new \Tk\Table\Cell\Text('barcode'), 'uid')
                ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, $obj, $value) {
                    /** @var $obj \Uni\Db\User */
                    $value = '';
                    if ($obj->getData()->has('barcode')) {
                        $value .= $obj->getData()->get('barcode');
                    }
                    return $value;
                });
        }

        $filter = array();
        if ($this->getAuthUser()->getInstitutionId()) {
            $filter['institutionId'] = $this->getAuthUser()->getInstitutionId();
        } else if ($this->getAuthUser()->isClient()) {
            $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        }
        if (empty($filter['type'])) {
            $filter['type'] = $this->getTargetType();
        }
        if (($this->getConfig()->isSubjectUrl() || $request->has('subjectId')) && $this->getConfig()->getSubjectId()) {
            $filter['subjectId'] = $this->getConfig()->getSubjectId();
        }
        $this->getTable()->setList($this->getTable()->findList($filter));


    }

    /**
     *
     */
    public function initActionPanel()
    {
        if (
            ($this->getTargetType() == User::TYPE_STAFF && $this->getConfig()->getAuthUser()->hasPermission(\Uni\Db\Permission::MANAGE_STAFF)) ||
            ($this->getTargetType() == User::TYPE_STUDENT && $this->getConfig()->getAuthUser()->isCoordinator()) ||
            $this->getConfig()->getAuthUser()->isClient() || $this->getConfig()->getAuthUser()->isAdmin()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Create ' . ucfirst($this->getTargetType()), $this->getTable()->getEditUrl(), 'fa fa-user-plus'));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('table', $this->table->show());

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
<div class="tk-panel" data-panel-icon="fa fa-users" var="table"></div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}