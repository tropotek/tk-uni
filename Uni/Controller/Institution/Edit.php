<?php
namespace Uni\Controller\Institution;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminEditIface
{

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;



    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->setPageTitle('Institution Edit');

        if (!$this->institution) {
            $this->institution = $this->getConfig()->createInstitution();
            if ($request->get('institutionId')) {
                $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->get('institutionId'));
            }
            if ($this->getUser()->isClient()) {
                $this->institution = $this->getConfig()->getInstitutionMapper()->findByUserId($this->getUser()->getId());
            }
            $this->institution->getUser();
        }

        $this->setForm(\Uni\Form\Institution::create()->setModel($this->institution));
        $this->getForm()->execute();

    }

    /**
     * @return \Tk\Db\ModelInterface|\Uni\Db\Institution
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        if ($this->getConfig()->getMasqueradeHandler()->canMasqueradeAs($this->getUser(), $this->getInstitution()->getUser())) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Masquerade',
                \Uni\Uri::create()->reset()->set(\Uni\Listener\MasqueradeHandler::MSQ, $this->getInstitution()->getUser()->getHash()),
                'fa fa-user-secret'))->addCss('tk-masquerade')->setAttr('data-confirm', 'You are about to masquerade as the selected user?');
        }

        if ($this->getUser()->isClient() || $this->getUser()->isStaff()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Plugins',
                \Uni\Uri::createHomeUrl('/institution/'.$this->getInstitution()->getId().'/plugins.html'), 'fa fa-plug'));

            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Staff',
                \Uni\Uri::createHomeUrl('/staffUserManager.html'), 'fa fa-users'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Student',
                \Uni\Uri::createHomeUrl('/studentUserManager.html'), 'fa fa-users'));

            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Subjects',
                \Uni\Uri::createHomeUrl('/subjectManager.html'), 'fa fa-graduation-cap'));

        }

        $template = parent::show();

        // Render the form
        $template->appendTemplate('form', $this->getForm()->show());

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
<div>

  <div class="tk-panel" data-panel-icon="fa fa-university" var="form"></div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}