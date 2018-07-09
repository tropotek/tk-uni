<?php


$config = \Uni\Config::getInstance();
$data = \Tk\Db\Data::create();

$data->set('site.title', 'Uni Base Project');
$data->set('site.email', 'fvas-elearning@unimelb.edu.au');

$data->save();





