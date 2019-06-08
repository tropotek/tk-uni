<?php
namespace Uni\Controller\User;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminEditIface
{
    /**
     * Setup the controller to work with users of this role
     * @var string
     */
    protected $targetRole = '';

    /**
     * @var \Uni\Db\User
     */
    protected $user = null;


    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('User Edit');
    }

    /**
     * @param \Tk\Request $request
     * @param string $targetRole
     * @throws \Exception
     */
    public function doDefaultRole(\Tk\Request $request, $targetRole)
    {
        $this->targetRole = $targetRole;
        $this->doDefault($request);
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        switch($this->targetRole) {
            case \Uni\Db\Role::TYPE_ADMIN:
                $this->setPageTitle('Admin Edit');
                break;
            case \Uni\Db\Role::TYPE_COORDINATOR:
                $this->setPageTitle('Staff Edit');
                break;
            case \Uni\Db\Role::TYPE_STUDENT:
                $this->setPageTitle('Student Edit');
                break;
        }


        $this->user = $this->getConfig()->createUser();
        if ($this->targetRole != \Uni\Db\Role::TYPE_ADMIN && $this->targetRole != \Uni\Db\Role::TYPE_CLIENT) {
            $this->user->institutionId = $this->getConfig()->getInstitutionId();
        }
        $this->user->roleId = \Uni\Db\Role::getDefaultRoleId($this->targetRole);

        if ($request->has('userId')) {
            $this->user = $this->getConfig()->getUserMapper()->find($request->get('userId'));
            if (!$this->user)
                throw new \Tk\Exception('Invalid user account.');
            if ($this->getUser()->isStaff() && $this->getUser()->institutionId != $this->user->institutionId)
                throw new \Tk\Exception('Invalid system details');
        }

        $this->setForm(\Uni\Form\User::create()->setTargetRole($this->targetRole)->setModel($this->user));
        $this->getForm()->execute();

    }

    public function initActionPanel()
    {
        if ($this->user->getId() && $this->getConfig()->getMasqueradeHandler()->canMasqueradeAs($this->getUser(), $this->user)) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Masquerade',
                \Uni\Uri::create()->reset()->set(\Uni\Listener\MasqueradeHandler::MSQ, $this->user->hash), 'fa fa-user-secret'))
                ->setAttr('data-confirm', 'You are about to masquerade as the selected user?')->addCss('tk-masquerade');
        }
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->form->show());
        
        if ($this->user->id) {
            $template->setAttr('panel', 'data-panel-title', $this->user->name . ' - [UID ' . $this->user->getId() . ']');
        } else {
            $template->setAttr('panel', 'data-panel-title', 'Create User');
        }
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
<div class="tk-panel" data-panel-icon="fa fa-user" var="panel"></div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}