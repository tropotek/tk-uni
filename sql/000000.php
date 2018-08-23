<?php


$config = \Uni\Config::getInstance();
$data = \Tk\Db\Data::create();

if(!$data->has('site.title')) {
    $data->set('site.title', 'Uni Base Project');
    $data->set('site.short.title', 'Tk2Uni');
}
if(!$data->has('site.email'))
    $data->set('site.email', 'fvas-elearning@unimelb.edu.au');

$data->save();





