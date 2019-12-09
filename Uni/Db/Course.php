<?php
namespace Uni\Db;

use Bs\Db\Traits\TimestampTrait;
use Uni\Config;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2019-12-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Course extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use InstitutionTrait;
    use TimestampTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var int
     */
    public $coordinatorId = 0;

    /**
     * @var string
     */
    public $code = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $emailSignature = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var null|User
     */
    private $_coordinator = null;


    /**
     * Course
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @return \Tk\Db\Map\Model|\Tk\Db\ModelInterface|User|null
     */
    public function getCoordinator()
    {
        if (!$this->_coordinator) {
            try {
                $this->_coordinator = Config::getInstance()->getUserMapper()->find($this->getCoordinatorId());
            } catch (\Exception $e) {}
        }
        return $this->_coordinator;
    }

    /**
     * @param int $coordinatorId
     * @return Course
     */
    public function setCoordinatorId($coordinatorId) : Course
    {
        $this->coordinatorId = $coordinatorId;
        return $this;
    }

    /**
     * return int
     */
    public function getCoordinatorId() : int
    {
        return $this->coordinatorId;
    }

    /**
     * @param string $code
     * @return Course
     */
    public function setCode($code) : Course
    {
        $this->code = $code;
        return $this;
    }

    /**
     * return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * @param string $name
     * @return Course
     */
    public function setName($name) : Course
    {
        $this->name = $name;
        return $this;
    }

    /**
     * return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $email
     * @return Course
     */
    public function setEmail($email) : Course
    {
        $this->email = $email;
        return $this;
    }

    /**
     * return string
     */
    public function getEmail() : string
    {
        if ($this->email)
            return $this->email;
        if ($this->getCoordinator() && $this->getCoordinator()->getEmail())
            return $this->getCoordinator()->getEmail();
        return '';
    }

    /**
     * @param string $emailSignature
     * @return Course
     */
    public function setEmailSignature($emailSignature) : Course
    {
        $this->emailSignature = $emailSignature;
        return $this;
    }

    /**
     * return string
     */
    public function getEmailSignature() : string
    {
        return $this->emailSignature;
    }

    /**
     * @param string $description
     * @return Course
     */
    public function setDescription($description) : Course
    {
        $this->description = $description;
        return $this;
    }

    /**
     * return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param \DateTime $modified
     * @return Course
     */
    public function setModified($modified) : Course
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * return \DateTime
     */
    public function getModified() : \DateTime
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $created
     * @return Course
     */
    public function setCreated($created) : Course
    {
        $this->created = $created;
        return $this;
    }

    /**
     * return \DateTime
     */
    public function getCreated() : \DateTime
    {
        return $this->created;
    }
    
    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->coordinatorId) {
            $errors['coordinatorId'] = 'Invalid value: coordinatorId';
        }

        if (!$this->code) {
            $errors['code'] = 'Invalid value: code';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->email) {
            $errors['email'] = 'Invalid value: email';
        }

        return $errors;
    }

}
