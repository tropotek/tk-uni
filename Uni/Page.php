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
     * Create the default template path using the url role if available (see Config)
     *
     * @return string
     */
    protected function makeDefaultTemplatePath()
    {
        $path = parent::makeDefaultTemplatePath();
        // TODO: should this be in the LTI plugin va a handler somewhere?
        if ($this->getConfig()->isLti() && $this->getConfig()->has('template.lti')) {
            $path = $this->getConfig()->getSitePath() . $this->getConfig()->get('template.lti');
        }
        return $path;
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