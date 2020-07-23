<?php
/**
 * @version 3.0
 *
 * @author: Michael Mifsud <info@tropotek.com>
 */

$config = \Uni\Config::getInstance();
try {
    $data = \Tk\Db\Data::create();
    if (!$data->get('site.title')) {
        $data->set('site.title', 'Anatomic Pathology Database');
        $data->set('site.short.title', 'APD');
    }
    if (!$data->get('site.email'))
        $data->set('site.email', 'anat-vet@unimelb.edu.au');

    $data->save();

} catch (\Exception $e) {}





