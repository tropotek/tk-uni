<?php
namespace Uni\Db\Traits;


use Tk\Db\ModelInterface;
use Tk\Db\Tool;
use Uni\Db\Status;
use Uni\Db\StatusMap;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 *
 * NOTE: The EMS still uses the uni\Db\Status system
 *       DO NOT REMOVE IT FROM EITHER LIBS YET!!!!!!
 *
 * @deprecated
 */
trait StatusTrait
{
    use \Bs\Db\Traits\StatusTrait;
/*  // exaple override
    use SoftDeletes {
        SoftDeletes::saveWithHistory as parentSaveWithHistory;
    }

    public function saveWithHistory() {
        $this->parentSaveWithHistory();

        //your implementation
    }
 */

    /**
     * @var null|Status
     */
    private $_statusObject = null;

    /**
     * Save the status and execute
     * NOTE: This is called by the StatusHandler DB post save event
     *
     * @throws \Exception
     */
    public function saveStatus()
    {
        if (!$this->getStatusEvent()) { // create a default status name
            $this->setStatusEvent($this->makeStatusEventName());
        }
        $this->_statusObj = \Uni\Db\Status::create($this);
        if ($this->isStatusExecute()) {
            $this->_statusObj->execute();   // <-- Status saved in here!!!
        }
    }

    /**
     * Object may not have a status
     *
     * @return Status|null
     */
    public function getStatusObject()
    {
        if (!$this->_statusObject) {
            $this->_statusObject = \Uni\Db\StatusMap::create()->findFiltered(array(
                'fkey' => get_class($this),
                'fid' => $this->getId(),
                'status' => $this->getStatus()
            ), \Tk\Db\Tool::create('`created` DESC'))->current();
        }
        return $this->_statusObject;
    }
    /**
     * @return Status|null|ModelInterface
     * @throws /Exception
     */
    public function getCurrentStatus()
    {
        $status = StatusMap::create()->findFiltered(array('model' => $this), Tool::create('created DESC', 1))->current();
        return $status;
    }
}