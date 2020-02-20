<?php

$config = \Uni\Config::getInstance();
try {
    $data = \Tk\Db\Data::create();
    if (!$data->get('site.title')) {
        $data->set('site.title', 'Uni Base Project');
        $data->set('site.short.title', 'TkUni');
    }
    if (!$data->get('site.email'))
        $data->set('site.email', 'fvas-elearning@unimelb.edu.au');

    if (!$data->get('site.meta.keywords'))
        $data->set('site.meta.keywords', '');
    if (!$data->get('site.meta.description'))
        $data->set('site.meta.description', '');
    if (!$data->get('site.global.js'))
        $data->set('site.global.js', '');
    if (!$data->get('site.global.css'))
        $data->set('site.global.css', '');

    $data->save();

} catch (\Exception $e) {}





