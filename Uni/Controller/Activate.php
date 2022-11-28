<?php
namespace Uni\Controller;

use Bs\Uri;
use Tk\Alert;
use Tk\Request;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Activate extends \Bs\Controller\Activate
{

    /**
     * @var \Uni\Db\Institution
     */
    protected $institution = null;



    /**
     * @param \Tk\Request $request
     * @param string $instHash
     * @throws \Exception
     */
    public function doInsActivate(\Tk\Request $request, $instHash = '')
    {
        $this->institution = $this->getConfig()->getInstitutionMapper()->findByHash($instHash);
        if (!$this->institution && $request->attributes->has('institutionId')) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->attributes->get('institutionId'));
        }
        // get institution by hostname
        if (!$this->institution || !$this->institution->active ) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->findByDomain($request->getTkUri()->getHost());
        }
        // Get the first available institution
        if (!$this->institution || !$this->institution->active ) {
            $this->institution = $this->getConfig()->getInstitutionMapper()->findFiltered(array())->current();
        }

        if (!$this->institution || !$this->institution->active ) {
            \Tk\Alert::addWarning('Invalid or inactive Institution. Setup an active institution to continue.');
            \Uni\Uri::create('/index.html')->redirect();
        }

        $this->doDefault($request);
    }

    public function doDefault(Request $request)
    {
        $hash = $request->get('h');
        $this->user = $this->getConfig()->getUserMapper()->findByHash($hash, $this->institution->getId());
        if (!$this->user || $this->user->getPassword()) {       // Only allow access for users without a password set
            Alert::addError('Invalid user account');
            Uri::create('/')->redirect();
        }

        $this->init();

        if ($this->institution) {
            $this->form->appendField(new Field\Hidden('instHash', $this->institution->getHash()));
        }

        $this->form->execute();

    }

    public function getLoginUrl()
    {
        return \Uni\Uri::createInstitutionUrl($this->getConfig()->get('url.auth.login'), $this->institution);
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();
        if ($this->institution) {
            if ($this->institution->getLogoUrl()) {
                $template->setVisible('instLogo');
                $template->setAttr('instLogo', 'src', $this->institution->getLogoUrl()->toString());
            }
            $template->insertText('instName', $this->institution->name);
            $template->setVisible('inst');
        }


        return $template;
    }


    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-login-panel tk-login">
  <p>Please create a new password to access your account.</p>
  <p><small>Passwords must be longer than 8 characters and include one number, one uppercase letter and one symbol.</small></p>
  <div var="form"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}