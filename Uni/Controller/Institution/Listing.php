<?php
namespace Uni\Controller\Institution;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Listing extends \Uni\Controller\AdminIface
{

    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Institutions');
        $this->getActionPanel()->setVisible(false);
    }

    /**
     * doDefault
     *
     * @param Request $request
     */
    public function doDefault(Request $request)
    {

    }

    /**
     * show()
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::getTemplate();

        $list = \Uni\Db\InstitutionMap::create()->findFiltered(array(
            'active' => true
        ));
        foreach ($list as $institution) {
            $loginUrl = \Uni\Uri::createInstitutionUrl('/login.html', $institution);
            if ($institution->getDomain()) {
                $loginUrl = \Uni\Uri::create('/login.html');
                $loginUrl->setHost($institution->getDomain());
            }

            $row = $template->getRepeat('ins-row');
            $row->insertText('name', $institution->name);
            $row->insertText('name-extra', $institution->email);
            $row->insertText('title', $institution->name);
            $row->setAttr('title', 'href', $loginUrl);
            $row->setAttr('login-url', 'href', $loginUrl);

            $row->appendHtml('description', \Tk\Str::wordcat(strip_tags($institution->description), 200));

            if ($institution->getLogoUrl()) {
                $row->setAttr('image', 'src', $institution->getLogoUrl());
                $row->setChoice('image');
            }
            $row->appendRepeat();

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
<div class="institution-list">
  
  <section>

    <div class="row inst-list">
    
      <div class="col-md-4" repeat="ins-row" var="ins-row">
        <div class="institution-cell">
          <h3><a href="#" var="title"></a></h3>
          <div var="description"></div>
          <div class="team-member-social clearfix">
            <a href="#" class="btn btn-primary" var="login-url"><i class="fa fa-sign-in"></i> Login</a>
          </div>
        </div>
      </div>

    </div>
  </section> 
  
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}