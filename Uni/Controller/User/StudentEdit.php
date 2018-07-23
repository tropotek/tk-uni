<?php
namespace Uni\Controller\User;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StudentEdit extends Edit
{
    /**
     * Iface constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function setPageHeading()
    {
        $this->setPageTitle('Student Edit');
    }


}