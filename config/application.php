<?php
/*
 * Application default config values
 * This file should not need to be edited
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */

$config = \Uni\Config::getInstance();

/**************************************
 * Default app config values
 **************************************/

$config['site.title'] = 'Uni Base Template';
$config['site.email'] = 'user@example.com';

/*
 * Setup what paths to check when migrating SQL
 */
$config['sql.migrate.list'] = array(
    'Lib Sql' => $config->getVendorPath() . '/ttek/tk-uni',
    'Plugin Sql' => $config->getPluginPath(),
    'App Sql' => $config->getSrcPath() . '/config'
);

/*
 * Template folders for pages
 */
$config['system.template.path'] = '/html';

$config['system.theme.admin']   = $config['system.template.path'] . '/admin';
$config['system.theme.public']  = $config['system.template.path'] . '/admin';

$config['template.admin']       = $config['system.theme.admin'] . '/admin.html';
$config['template.client']      = $config['system.theme.admin'] . '/admin.html';
$config['template.staff']       = $config['system.theme.admin'] . '/admin.html';
$config['template.student']     = $config['system.theme.admin'] . '/admin.html';
$config['template.public']      = $config['system.theme.admin'] . '/public.html';

$config['template.error']       = dirname($config['template.admin']).'/error.html';



/*
 * ---- AUTH CONFIG ----
 */

/*
 * Should the system use a salted password?
 */
$config['system.auth.salted'] = true;


/**
 * This is to be set to the sites default domain name so we can use it for links from the
 * institution pages back to the site
 */
$config['site.public.domain'] = '';


/*
 * Config for the \Tk\Auth\Adapter\DbTable
 */
$config['system.auth.dbtable.tableName']      = 'user';
$config['system.auth.dbtable.usernameColumn'] = 'username';
$config['system.auth.dbtable.passwordColumn'] = 'password';
$config['system.auth.dbtable.saltColumn']     = 'hash';
$config['system.auth.dbtable.activeColumn']   = 'active';



