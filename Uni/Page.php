<?php
namespace Uni;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Page extends \Bs\Page
{

    /**
     * Set the page heading, should be set from main controller
     *
     * @return \Dom\Template
     * @throws \Tk\Exception
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        if ($this->getUser()) {
            $template->setAttr('dashUrl', 'href', \Uni\Uri::createHomeUrl('/index.html'));
        }

        if ($this->getUser()) {
            $institutionId = $this->getConfig()->getInstitutionId();
            $subjectId = $this->getConfig()->getSubjectId();
            $role = $this->getUser()->getRole();
            $js = <<<JS
config.subjectId = $subjectId;
config.institutionId = $institutionId;
config.role = '$role';
JS;
            $template->appendJs($js, array('data-jsl-priority' => -1000));
        }

        return $template;
    }

    /**
     * Get the currently logged in user
     *
     * @return \Uni\Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

    /**
     * Get the global config object.
     *
     * @return \Uni\Config
     */
    public function getConfig()
    {
        return \Uni\Config::getInstance();
    }

}