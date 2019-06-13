<?php
namespace Uni\Db;

use Tk\Db\Data;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Subject extends \Tk\Db\Map\Model implements \Uni\Db\SubjectIface
{
    use \Uni\Db\Traits\InstitutionTrait;
    use \Bs\Db\Traits\TimestampTrait;
    
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $code = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var \DateTime
     */
    public $dateStart = null;

    /**
     * @var \DateTime
     */
    public $dateEnd = null;

    /**
     * @var boolean
     */
    public $notify = true;

    /**
     * @var boolean
     */
    public $publish = true;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var Data
     */
    protected $data = null;


    /**
     * Subject constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->_TimestampTrait();

        $this->dateStart = \Tk\Date::floor()->setDate($this->created->format('Y'), 1, 1);
        $this->dateEnd = \Tk\Date::ceil()->setDate($this->created->format('Y'), 12, 31);
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        $this->code = self::cleanCode($this->code);
        $this->getData()->save();
        parent::save();
    }

    /**
     * Get the data object
     *
     * @return \Tk\Db\Data
     */
    public function getData()
    {
        if (!$this->data)
            $this->data = \Tk\Db\Data::create(get_class($this), $this->getVolatileId());
        return $this->data;
    }

    /**
     * Get the path for all file associated to this object
     *
     * @return string
     * @throws \Exception
     */
    public function getDataPath()
    {
        return sprintf('%s/subject/%s', $this->getInstitutionObj()->getDataPath(), $this->getVolatileId());
    }

    /**
     *
     * @param UserIface $user
     * @return mixed
     * @throws \Exception
     */
    public function isUserEnrolled($user)
    {
        return $this->getConfig()->getSubjectMapper()->hasUser($this->getId(), $user->getId());
    }

    /**
     * Enroll a user
     *
     * @param $user
     * @return $this
     * @throws \Exception
     */
    public function enrollUser($user)
    {
        if (!$this->isUserEnrolled($user)) {
            $this->getConfig()->getSubjectMapper()->addUser($this->getId(), $user->getId());
        }
        return $this;
    }

    /**
     * Clean a subject code to be compatible with LMS/LTI systems
     * valid chars are  [a-zA-Z0-9_-]
     *
     * @param $subjectCode
     * @return mixed
     */
    public static function cleanCode($subjectCode)
    {
        $s = preg_replace('/[^a-z0-9_-]/i', '_', $subjectCode);
        //$s = preg_replace('/__++/', '_', $s);     // TODO: check if this is needed
        return $s;
    }

    /**
     * @param $str
     * @return null|string|string[]
     */
    public static function incrementString($str)
    {
        // increment years

        $s = preg_replace_callback('/(.*)(20[0-9]{2})(.*)/', function ($regs) {
            if (count($regs) == 4) {
                return $regs[1] . ($regs[2]+1) . $regs[3];
            }
            return '';
        }, $str);
        if ($s) $str = $s;

        // increment semester
        $s = preg_replace_callback('/(.*SEM)([12])(.*)/', function ($regs) {
            if (count($regs) == 4) {
                $sem = '2';
                if ($regs[2] == '2') {
                    $sem = '1';
                }
                return $regs[1] . $sem . $regs[3];
            }
            return '';
        }, $str);
        if ($s) $str = $s;

        return $str;
    }

    /**
     * If false, the student should be denied access to creating new submissions of any type for that subject.
     * If false, the UI should display historic grades and placement data.
     *
     * @return bool
     */
    public function isActive()
    {
        if (!$this->dateStart || !$this->dateEnd) return true;
        $now = \Tk\Date::create();
        return (\Tk\Date::greaterThan($now, $this->dateStart) && \Tk\Date::lessThan($now, $this->dateEnd));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Subject
     */
    public function setName(string $name): Subject
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Subject
     */
    public function setCode(string $code): Subject
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Subject
     */
    public function setEmail(string $email): Subject
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Subject
     */
    public function setDescription(string $description): Subject
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart(): \DateTime
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     * @return Subject
     */
    public function setDateStart(\DateTime $dateStart): Subject
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd(): \DateTime
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     * @return Subject
     */
    public function setDateEnd(\DateTime $dateEnd): Subject
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNotify(): bool
    {
        return $this->notify;
    }

    /**
     * @param bool $notify
     * @return Subject
     */
    public function setNotify(bool $notify): Subject
    {
        $this->notify = $notify;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublish(): bool
    {
        return $this->publish;
    }

    /**
     * @return bool
     * @alias isPublish()
     */
    public function isPublished(): bool
    {
        return $this->publish;
    }

    /**
     * @param bool $publish
     * @return Subject
     */
    public function setPublish(bool $publish): Subject
    {
        $this->publish = $publish;
        return $this;
    }


//    /**
//     * @return string
//     */
//    public function getName()
//    {
//        return $this->name;
//    }
//
//    /**
//     * @return string
//     */
//    public function getCode()
//    {
//        return $this->code;
//    }
//
//    /**
//     * @return string
//     * @throws \Exception
//     */
//    public function getEmail()
//    {
//        return $this->email;
//    }
//
//    /**
//     * @return \DateTime
//     */
//    public function getDateStart()
//    {
//        return $this->dateStart;
//    }
//
//    /**
//     * @return \DateTime
//     */
//    public function getDateEnd()
//    {
//        return $this->dateEnd;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDescription()
//    {
//        return $this->description;
//    }
//
//    /**
//     * @param string $description
//     * @return Subject
//     */
//    public function setDescription($description)
//    {
//        $this->description = $description;
//        return $this;
//    }
//
//    /**
//     * if set to false the no email notifications should be sent for this subject
//     *
//     * @return bool
//     */
//    public function isNotify()
//    {
//        return $this->notify;
//    }
//
//    /**
//     * @param bool $notify
//     * @return Subject
//     */
//    public function setNotify($notify)
//    {
//        $this->notify = $notify;
//        return $this;
//    }
//
//    /**
//     * If false, students will not be able to access/view this subject and its or their data.
//     *
//     * @return bool
//     */
//    public function isPublish()
//    {
//        return $this->publish;
//    }
//
//    /**
//     * If false, students will not be able to access/view this subject and its or their data.
//     *
//     * @return bool
//     */
//    public function isPublished()
//    {
//        return $this->publish;
//    }
//
//    /**
//     * @param bool $publish
//     * @return Subject
//     */
//    public function setPublish($publish)
//    {
//        $this->publish = $publish;
//        return $this;
//    }

    /**
     * @return array
     * @throws \Exception
     */
    public function validate()
    {
        $errors = array();
        $errors = $this->validateInstitutionId($errors);

        if (!$this->getName()) {
            $errors['name'] = 'Please enter a valid name';
        }
        if (!$this->getCode()) {
            $errors['code'] = 'Please enter a valid code';
        } else {
            // Look for existing subjects with same code
            $c = $this->getConfig()->getSubjectMapper()->findByCode($this->getCode(), $this->getInstitutionId());
            if ($c && $c->getId() != $this->getId()) {
                $errors['code'] = 'Subject code already exists';
            }
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        return $errors;
    }

    /**
     * @return \Uni\Config|\Tk\Config
     */
    public function getConfig()
    {
        return parent::getConfig();
    }
}