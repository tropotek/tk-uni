<?php
namespace Uni\Db;

use Tk\Db\Data;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Institution extends \Tk\Db\Map\Model implements \Tk\ValidInterface, InstitutionIface
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $domain = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $street = '';

    /**
     * @var string
     */
    public $city = '';

    /**
     * @var string
     */
    public $state = '';

    /**
     * @var string
     */
    public $country = '';

    /**
     * @var string
     */
    public $postcode = '';

    /**
     * @var string
     */
    public $address = '';

    /**
     * @var float
     */
    public $mapLat = -37.797441;

    /**
     * @var float
     */
    public $mapLng = 144.960773;

    /**
     * @var float
     */
    public $mapZoom = 14.0;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $logo = '';

    /**
     * @var string
     */
    public $feature = '';

    /**
     * @var boolean
     */
    public $active = true;

    /**
     * @var string
     */
    public $hash = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var Data
     */
    protected $data = null;
    
    

    /**
     *
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->userId && $this->user) $this->userId = $this->getUser()->getId();
        $this->getHash();
        $this->getData()->save();
        parent::save();
    }

    /**
     * Find this institutions owner user
     *
     * @return User
     * @throws \Exception
     */
    public function getUser()
    {
        if (!$this->user) {
            if (!$this->getId()) {
                $this->user = \Uni\Config::getInstance()->createUser();
                $this->user->roleId = \Uni\Db\Role::getDefaultRoleId(\Uni\Db\Role::TYPE_CLIENT);
            } else {
                $this->user = \Uni\Config::getInstance()->getUserMapper()->find($this->userId);
            }
        }
        return $this->user;
    }

    /**
     * Get the user hash or generate one if needed
     *
     * @return string
     */
    public function getHash()
    {
        if (!$this->hash) {
            $this->hash = $this->generateHash();
        }
        return $this->hash;
    }

    /**
     * Helper method to generate user hash
     *
     * @return string
     */
    public function generateHash()
    {
        return hash('md5', sprintf('%s', $this->getVolatileId()));
    }

    /**
     * Get the path for all file associated to this object
     *
     * @return string
     */
    public function getDataPath()
    {
        return sprintf('/institution/%s', $this->getVolatileId());
    }

    /**
     * Get the institution data object
     *
     * @return Data
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = Data::create(get_class($this), $this->getVolatileId());
        return $this->data;
    }

    /**
     * Returns null if no logo available
     *
     * @return \Tk\Uri|null
     */
    public function getLogoUrl()
    {
        if ($this->logo)
            return \Tk\Uri::create(\Uni\Config::getInstance()->getDataUrl().$this->logo);
    }

    /**
     * Returns null if no feature available
     *
     * @return \Tk\Uri|null
     */
    public function getFeatureUrl()
    {
        if ($this->feature)
            return \Tk\Uri::create(\App\Config::getInstance()->getDataUrl().$this->feature);
    }

    /**
     * @return \Tk\Uri
     */
    public function getLoginUrl()
    {
        $loginUrl = \Uni\Uri::createInstitutionUrl('/login.html', $this);
        if ($this->getDomain()) {
            $loginUrl = \Uni\Uri::create('/login.html');
            $loginUrl->setHost($this->getDomain());
        }
        return $loginUrl;
    }

    /**
     * @param string $subjectCode
     * @return \Tk\Db\Map\Model|\Tk\Db\ModelInterface|SubjectIface|null
     * @throws \Exception
     */
    public function findSubjectByCode($subjectCode)
    {
        return \Uni\Config::getInstance()->getSubjectMapper()->findByCode($subjectCode, $this->getId());
    }

    /**
     * @param int $subjectId
     * @return \Tk\Db\Map\Model|\Tk\Db\ModelInterface|SubjectIface|null
     * @throws \Exception
     */
    public function findSubject($subjectId)
    {
        return \Uni\Config::getInstance()->getSubjectMapper()->find($subjectId);
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * Implement the validating rules to apply.
     *
     * @throws \Exception
     */
    public function validate()
    {
        $error = array();

        if (!$this->name) {
            $error['name'] = 'Invalid name value';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $error['email'] = 'Please enter a valid email address';
        }

        // Ensure the domain is unique if set.
        if ($this->domain) {
            //if (!preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/g', $obj->domain)) {
            if (!preg_match(self::REG_DOMAIN, $this->domain)) {
                $error['domain'] = 'Please enter a valid domain name (EG: example.com.au)';
            } else {
                $dup = InstitutionMap::create()->findByDomain($this->domain);
                if ($dup && $dup->getId() != $this->getId()) {
                    $error['domain'] = 'This domain name is already in use';
                }
            }
        }

        return $error;
    }
}
