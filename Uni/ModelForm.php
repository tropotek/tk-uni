<?php
namespace Uni;

/**
 * @author Tropotek <info@tropotek.com>
 * @created: 22/07/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
abstract class ModelForm extends \Bs\ModelForm
{


    /**
     * @return Config
     */
    public function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * @return Db\User
     */
    public function getUser()
    {
        return $this->getConfig()->getUser();
    }

}