<?php
namespace Uni\Db;

use Exception;
use Tk\Db\Map\Model;
use Tk\Db\ModelInterface;
use Tk\Db\Tool;
use Tk\Form\Field\Iface;
use Tk\Log;
use Tk\ObjectUtil;
use Bs\Db\Traits\CreatedTrait;
use Bs\Db\Traits\ForegnModelTrait;
use Bs\Db\Traits\UserTrait;
use DateTime;
use Uni\Event\StatusEvent;
use Uni\Form\Field\CheckSelect;
use Uni\Form\Field\StatusSelect;
use Uni\StatusEvents;
use Uni\Db\Traits\CourseTrait;
use Uni\Db\Traits\SubjectTrait;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Status extends Model
{
    use UserTrait;
    use CourseTrait;
    use SubjectTrait;
    use ForegnModelTrait;
    use CreatedTrait;

    // Status type templates (use these in your own objects)
    // const STATUS_PENDING = Status::STATUS_PENDING;       <---- This is valid syntax in you objects.
    const STATUS_PENDING = 'pending';
    const STATUS_AMEND = 'amend';
    const STATUS_APPROVED = 'approved';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_NOT_APPROVED = 'not approved';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @var int
     */
    public $id = 0;

    /**
     * The user who performed the activity
     * @var int
     */
    public $userId = 0;

    /**
     * If the user was masquerading who was the root masquerading user
     * @var int
     */
    public $msqUserId = 0;

    /**
     * (Required)
     * @var int
     */
    public $courseId = 0;

    /**
     * (optional) If this is 0 then it is assumed by the system that
     * all subjects have access to change/view the status of this model.
     * See the \App\Ui\Table\StatusPending
     * @var int
     */
    public $subjectId = 0;

    /**
     * The id of the subject of the activity
     * @var int
     */
    public $fid = 0;

    /**
     * The object class/key the foreign_id relates to
     * @var string
     */
    public $fkey = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * The name of the event if triggered, '' for none
     * @var string
     */
    public $event = '';

    /**
     * Should this status trigger the mail notification handler
     * @var bool
     */
    public $notify = false;

    /**
     * @var string
     */
    public $message = '';

    /**
     * objects or array of objects
     * @var mixed
     */
    public $serialData = null;

    /**
     * @var DateTime
     */
    public $created = null;


    /**
     * @var ModelInterface|StatusStrategyInterface
     */
    private $_modelStrategy = null;

    /**
     * @var Status
     */
    private $_previous = null;


    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->_CreatedTrait();
    }

    /**
     * @param ModelInterface $model
     * @param StatusSelect|Iface $field
     * @param int $courseId
     * @param int $subjectId
     * @param string $event
     * @return Status
     * @throws Exception
     */
    public static function createFromField($model, StatusSelect $field, $courseId = null, $subjectId = null, $event = '')
    {
        return self::create($model, $field->getValue(), $field->isChecked(), $field->getNotesValue(), $courseId, $subjectId, $event);
    }

    /**
     * @param ModelInterface $model
     * @param string $statusName
     * @param bool $notify
     * @param string $message
     * @param null|int $courseId
     * @param null|int $subjectId
     * @param string $event
     * @return Status
     * @throws Exception
     */
    public static function create($model, $statusName, $notify = false, $message = '', $courseId = null, $subjectId = null, $event = '')
    {
        $obj = static::createDetached($model, $statusName, $notify, $message, $courseId, $subjectId, $event);
        // This is needed so the getPreviousStatus method works as expected???? <----- UPDATE: I do not think this statement is true!!
        $obj->execute();
        return $obj;
    }

    /**
     * Same as create but does not execute the status or save it to the DB
     *
     * @param ModelInterface $model
     * @param string $statusName
     * @param bool $notify
     * @param string $message
     * @param null $courseId
     * @param null $subjectId
     * @param string $event
     * @return static
     * @throws Exception
     */
    public static function createDetached($model, $statusName, $notify = false, $message = '', $courseId = null, $subjectId = null, $event = '')
    {
        $obj = new static();
        $config = $obj->getConfig();
        $obj->setModel($model);
        if ($config->getUser()) {
            $obj->setUserId($config->getUser()->getId());
            if ($config->getMasqueradeHandler()->isMasquerading()) {
                $msqUser = $config->getMasqueradeHandler()->getMasqueradingUser();
                if ($msqUser) {
                    $obj->setMsqUserId($msqUser->getId());
                }
            }
        }
        $obj->setName($statusName);
        $obj->setEvent($event);
        $obj->setNotify($notify);
        $obj->setMessage($message);

        if ($subjectId) {
            if ($subjectId instanceof Subject) {
                if (!$courseId) {
                    $obj->setCourseId($subjectId->getCourseId());
                }
                $subjectId = $subjectId->getId();
            }
            $obj->setSubjectId($subjectId);
        }
        if ($courseId) {
            if ($courseId instanceof Course) {
                $courseId = $courseId->getId();
            }
            $obj->setCourseId($courseId);
        }

        return $obj;
    }


    /**
     * @throws Exception
     */
    protected function execute()
    {
        if (!$this->getName() || $this->getName() == $this->getPreviousName()) {
            Log::debug('Status skipped');
            return;
        }
        $this->save();

        $modelStrat = $this->getModelStrategy();
        if (!$modelStrat instanceof StatusStrategyInterface) {
            Log::warning('Status model does not implement StatusStrategyInterface');
            return;
        }

        // Trigger mail event depending on the model
        if ($modelStrat->triggerStatusChange($this)) {
            if (!$this->getEvent()) {
                $this->setEvent($this->makeEventName());
                $this->save();
            }
            // Trigger Event
            $e = new StatusEvent($this);
            if ($this->getConfig()->getEventDispatcher()) {
                // Fire event to setup status mail messages
                $this->getConfig()->getEventDispatcher()->dispatch(StatusEvents::STATUS_CHANGE, $e);
                // Trigger status events for system wide processing. EG: 'status.placement.not approved', status.placementrequest.pending'
                $this->getConfig()->getEventDispatcher()->dispatch($this->getEvent(), $e);
                // Fire the event to send those messages
                $this->getConfig()->getEventDispatcher()->dispatch(StatusEvents::STATUS_SEND_MESSAGES, $e);
            }
        }
    }

    /**
     * Get the lowercase event name produced by the object class name and status name
     *
     * @return string
     */
    public function makeEventName()
    {
        if (!$this->getName()) return '';
        $evt = strtolower('status.' . ObjectUtil::getBaseNamespace($this->getFkey()) . '.' . ObjectUtil::basename($this->getFkey()) . '.' . $this->getName());
        //$evt = strtolower('status.' . ObjectUtil::basename($this->getFkey()) . '.' . $this->getName());
        return $evt;
    }

    /**
     *
     * @return null|StatusStrategyInterface
     */
    public function getModelStrategy()
    {
        $class = $this->getModelStrategyClass();
        if (!$this->_modelStrategy && class_exists($class)) {
            $this->_modelStrategy = new $class($this);
        }
        return $this->_modelStrategy;
    }

    /**
     *
     * @return string
     */
    public function getModelStrategyClass()
    {
        return $this->getFkey() . 'StatusStrategy';
    }

    /**
     * @return Status|null|ModelInterface|StatusStrategyInterface
     * @throws Exception
     */
    public function getPrevious()
    {
        if (!$this->_previous) {
            $filter = array(
                'before' => $this->getCreated(),
                'courseId' => $this->getCourseId(),
                'fid' => $this->getFid(),
                'fkey' => $this->getFkey()
            );
            $this->_previous = StatusMap::create()->findFiltered($filter, Tool::create('created DESC', 1))->current();
        }
        return $this->_previous;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPreviousName()
    {
        if ($this->getPrevious())
            return $this->getPrevious()->getName();
        return '';
    }

    /**
     * @return User|\Bs\Db\UserIface|null
     * @throws Exception
     */
    public function findLastStudent()
    {
        $s = self::findLastByUserRole($this, Permission::TYPE_STUDENT);
        if ($s)
            return $s->getUser();
        return null;
    }

    /**
     * @return User|\Bs\Db\UserIface|null
     * @throws Exception
     */
    public function findLastStaff()
    {
        $s = self::findLastByUserRole($this, Permission::TYPE_STAFF);
        if ($s)
            return $s->getUser();
        return null;
    }

    /**
     * Get some valid subject name text
     * @return string
     * @throws Exception
     */
    public function getSubjectName()
    {
        $subjectName = $this->getCourse()->getName();
        if ($this->getSubject()) {
            $subjectName = $this->getSubject()->getName();
        }
        return $subjectName;
    }

    /**
     * @param Status|null $status
     * @param string $roleType
     * @return Status|null
     * @throws Exception
     */
    public static function findLastByUserRole($status, $roleType)
    {
        if (!$status) return $status;
        if ($status->getUser() && $status->getUser()->hasPermission($roleType)) {
            return $status;
        }
        return self::findLastByUserRole($status->getPrevious(), $roleType);
    }

    /**
     * @return int
     */
    public function getMsqUserId(): int
    {
        return $this->msqUserId;
    }

    /**
     * @param int $msqUserId
     * @return Status
     */
    public function setMsqUserId(int $msqUserId): Status
    {
        $this->msqUserId = $msqUserId;
        return $this;
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
     * @return Status
     */
    public function setName(string $name): Status
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @return Status
     */
    public function setEvent(string $event): Status
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return string
     */
    public function isNotify(): string
    {
        return $this->notify;
    }

    /**
     * @param string $notify
     * @return Status
     */
    public function setNotify(string $notify): Status
    {
        $this->notify = $notify;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Status
     */
    public function setMessage(string $message): Status
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSerialData()
    {
        return $this->serialData;
    }

    /**
     * @param mixed $serialData
     * @return Status
     */
    public function setSerialData($serialData)
    {
        $this->serialData = $serialData;
        return $this;
    }

}