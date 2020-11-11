<?php
namespace Uni\Db;

use Bs\Db\Traits\ForeignKeyTrait;
use Bs\Db\Traits\ForeignModelTrait;
use Exception;
use Tk\Db\Map\Model;
use Tk\Db\ModelInterface;
use Tk\Db\Tool;
use Tk\Form\Field\Iface;
use Tk\Log;
use Tk\ObjectUtil;
use Bs\Db\Traits\CreatedTrait;
use Bs\Db\Traits\UserTrait;
use DateTime;
use Uni\Db\Traits\StatusTrait;
use Uni\Event\StatusEvent;
use Uni\Form\Field\StatusSelect;
use Uni\StatusEvents;
use Uni\Db\Traits\CourseTrait;
use Uni\Db\Traits\SubjectTrait;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * TODO: Extend the \Bs\Db\Status sometime using the Config to get new instances
 */
class Status extends \Bs\Db\Status
{
    use CourseTrait;
    use SubjectTrait;

    /**
     * (Required)
     * TODO: Can we make this optional or create a base Status class
     *      So we can use it in non-university applications (IE: the jobsystem)
     * @var int
     */
    public $courseId = 0;

    /**
     * (optional) If this is 0 then it is assumed by the system that
     * all subjects have access to change/view the status of this model.
     * See the \App\Ui\Table\StatusPending
     *
     * // TODO: see the above comment  ^^^^^^^^^
     *
     * @var int
     */
    public $subjectId = 0;



    /**
     * @param ModelInterface|StatusTrait $model
     * @param StatusSelect|Iface $field
     * @param string $event
     * @param boolean $execute          (True) Execute the status after creation
     * @return Status
     * @throws Exception
     */
    public static function createFromStatusSelect($model, StatusSelect $field, $event = '', $execute = true)
    {
        $obj = self::create($model, $field->getValue());
        $obj->setNotify($field->isChecked());
        $obj->setMessage($field->getNotesValue());
        $obj->setEvent($event);
        if ($execute)
            $obj->execute();
        return $obj;
    }

    /**
     * @param ModelInterface|StatusTrait $model
     * @param string $message
     * @param boolean $notify
     * @param string $event
     * @param boolean $execute          (True) Execute the status after creation
     * @return Status
     * @throws Exception
     */
    public static function createFromTrait($model, $message = '', $notify = true, $event = '', $execute = true)
    {
        $obj = self::create($model, $model->getStatus());
        $obj->setMessage($message);
        $obj->setNotify($notify);
        $obj->setEvent($event);
        if ($execute)
            $obj->execute();
        return $obj;
    }

    /**
     * @param ModelInterface|StatusTrait $model
     * @param string $name
     * @return Status
     * @throws Exception
     */
    public static function create($model, $name = '')
    {
        $obj = new static();
        $obj->setForeignModel($model);
        $obj->setName($model->getStatus());
        $obj->setEvent($model->getStatusEvent());

        $config = $obj->getConfig();
        if ($config->getAuthUser()) {
            $obj->setUserId($config->getAuthUser()->getId());
            if ($config->getMasqueradeHandler()->isMasquerading()) {
                $msqUser = $config->getMasqueradeHandler()->getMasqueradingUser();
                if ($msqUser) {
                    $obj->setMsqUserId($msqUser->getId());
                }
            }
        }
        // Auto set the subject and Course ID's if possible, when not possible execute should be set to false and set manually
        if (method_exists($model, 'getCourseId')) {
            $obj->setCourseId($model->getCourseId());
        } else if ($obj->getConfig()->getCourseId()) {
            $obj->setCourseId($obj->getConfig()->getCourseId());
        }
        if (method_exists($model, 'getSubjectId')) {
            $obj->setSubjectId($model->getSubjectId());
            if (!$obj->getCourseId() && method_exists($model, 'getSubject')) {
                /** @var Subject $subject */
                $subject = $model->getSubject();
                $obj->setCourseId($subject->getCourseId());
            }
        } else if ($obj->getConfig()->getSubjectId()) {
            $obj->setSubjectId($obj->getConfig()->getSubjectId());
        }
        return $obj;
    }



    /**
     * Trigger status change events and save the status object.
     * @throws Exception
     */
    public function execute()
    {
        parent::execute();

//        if (!$this->getName() || $this->getName() == $this->getPreviousName()) {
//            Log::debug('Status skipped');
//            return;
//        }
//        $this->save();
//
////        $model = $this->getModel();
////        if (!\Tk\ObjectUtil::classUses($model, StatusTrait::class)) {
//        $modelStrat = $this->getModelStrategy();
//        if (!$modelStrat instanceof StatusStrategyInterface) {
//            Log::warning(get_class($modelStrat) . ' does not implement StatusStrategyInterface');
//            return;
//        }
//
//        // Trigger mail event depending on the model
//        if ($modelStrat->triggerStatusChange($this)) {
//            if (!$this->getEvent()) {
//                $this->setEvent($this->makeEventName());
//                $this->save();
//            }
//            // Trigger Event
//            $e = new StatusEvent($this);
//            if ($this->getConfig()->getEventDispatcher()) {
//                // Fire event to setup status mail messages
//                $this->getConfig()->getEventDispatcher()->dispatch(StatusEvents::STATUS_CHANGE, $e);
//                // Trigger status events for system wide processing. EG: 'status.placement.not approved', status.placementrequest.pending'
//                $this->getConfig()->getEventDispatcher()->dispatch($this->getEvent(), $e);
//                // Fire the event to send those messages
//                $this->getConfig()->getEventDispatcher()->dispatch(StatusEvents::STATUS_SEND_MESSAGES, $e);
//            }
//        }
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
     * TODO: we should remove this in favor of having a setting/method in the config??? It needs to come from a configurable place
     * @return null|StatusStrategyInterface
     */
    public function getModelStrategy()
    {
        $class = $this->getModelStrategyClass();
        if ($class && !$this->_modelStrategy) {
            $this->_modelStrategy = new $class($this);
        }
        return $this->_modelStrategy;
    }

    /**
     * TODO: we should remove this in favor of having a setting/method in the config??? It needs to come from a configurable place
     * @return string
     */
    public function getModelStrategyClass()
    {
        $class = $this->getFkey() . 'StatusStrategy';
        if (class_exists($class))
            return $class;
        $class = $this->getFkey() . 'Strategy';
        if (class_exists($class))
            return $class;
        return '';
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
     * @return User|\Bs\Db\UserIface|null
     * @throws Exception
     */
    public function findLastStudent()
    {
        $s = self::findLastByUserType($this, User::TYPE_STUDENT);
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
        $s = self::findLastByUserType($this, User::TYPE_STAFF);
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
     * @param string $userType
     * @return Status|null
     * @throws Exception
     */
    public static function findLastByUserType($status, $userType)
    {
        if (!$status) return $status;
        if ($status->getUser() && $status->getUser()->hasType($userType)) {
            return $status;
        }
        return self::findLastByUserType($status->getPrevious(), $userType);
    }

    /**
     * Return a unique list of users that have changed a status for this object
     *
     * @param string|null $type
     * @return array
     * @throws Exception
     */
    public function findUsersByType($type = '')
    {
        $userList = array();
        $statusList = StatusMap::create()->findFiltered(array('model' => $this->getModel()));
        foreach ($statusList as $status) {
            if (!$status->getUser()) continue;
            if ($type && $status->getUser()->getType() == $type) {
                $userList[$status->getUserId()] = $status->getUser();
            }
        }
        return $userList;
    }


}