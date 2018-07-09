<?php
namespace Uni;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Bootstrap extends \Bs\Bootstrap
{


    /**
     * This will also load dependant objects into the config, so this is the DI object for now.
     *
     * @return \Uni\Config|\Bs\Config
     * @throws \Exception
     */
    public function init()
    {
        return parent::init();
    }

    /**
     * Init the application config files
     * @return \Uni\Config
     */
    public function initConfig()
    {
        $config = \Uni\Config::getInstance();
        include($config->getLibBasePath() . '/config/application.php');
        include($config->getLibUniPath() . '/config/application.php');
        if (is_file($config->getSrcPath() . '/config/application.php'))
            include($config->getSrcPath() . '/config/application.php');
        if (is_file($config->getSrcPath() . '/config/application.php'))
            include($config->getSrcPath() . '/config/application.php');
        return $config;
    }

    /**
     * Load the routes
     */
    public function addRoutes()
    {
        $config = \Uni\Config::getInstance();
        include($config->getLibBasePath() . '/config/routes.php');
        include($config->getLibUniPath() . '/config/routes.php');
        if (is_file($config->getSrcPath() . '/config/routes.php'))
            include($config->getSrcPath() . '/config/routes.php');
    }

}

