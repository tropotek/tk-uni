<?php


$config = \Uni\Config::getInstance();
$data = \Tk\Db\Data::create();

if(!$data->has('site.title'))
    $data->set('site.title', 'Uni Base Project');
if(!$data->has('site.email'))
    $data->set('site.email', 'fvas-elearning@unimelb.edu.au');

$data->save();





