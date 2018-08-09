<?php
namespace Uni;

/**
 * @author Tropotek <info@tropotek.com>
 * @created: 22/07/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
abstract class FormIface extends \Bs\FormIface
{
    /**
     * @var null|\Tk\Db\ModelInterface
     */
    protected $model = null;


    /**
     * @param string $formId
     */
    public function __construct($formId = '')
    {
        if (!$formId) $formId = 'uni-form';
        parent::__construct($formId);
    }

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