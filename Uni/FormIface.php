<?php
namespace Uni;

/**
 * @author Tropotek <info@tropotek.com>
 * @created: 22/07/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
class FormIface extends \Bs\FormIface
{
    /**
     * @var null|\Tk\Db\ModelInterface
     */
    protected $model = null;



    /**
     * @param string $formId
     * @param string $method
     * @param string|\Tk\Uri|null $action
     */
    public function __construct($formId = 'uni-form', $method = self::METHOD_POST, $action = null)
    {
        parent::__construct($formId, $method, $action);
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