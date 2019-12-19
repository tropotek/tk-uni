<?php
namespace Uni\Db\Traits;

use Uni\Config;
use Uni\Db\CourseIface;
use Uni\Db\Course;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait StatusTrait
{

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = (int)$status;
        return $this;
    }

    /**
     * Check if this object has a status
     *
     * @param string|array $status
     * @return bool
     */
    public function hasStatus($status)
    {
        if (!is_array($status)) $status = array($status);
        return in_array($this->getStatus(), $status);
    }

    /**
     * return the status list for a select field
     * @param null|string $status
     * @return array
     */
    public static function getStatusList($status = null)
    {
        $arr = \Tk\Form\Field\Select::arrayToSelectList(\Tk\ObjectUtil::getClassConstants(__CLASS__, 'STATUS'));
        if (is_string($status)) {
            $arr2 = array();
            foreach ($arr as $k => $v) {
                if ($v == $status) {
                    $arr2[$k.' (Current)'] = $v;
                } else {
                    $arr2[$k] = $v;
                }
            }
            $arr = $arr2;
        }
        return $arr;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateStatus($errors = [])
    {
        if (!$this->getStatus()) {
            $errors['status'] = 'Invalid value: status';
        }
        return $errors;
    }

    // TODO: We can look into implementing the following one the Status system is moved into the Uni lib


//    /**
//     * @return Status|null|ModelInterface
//     * @throws Exception
//     */
//    public function getCurrentStatus()
//    {
//        $status = StatusMap::create()->findFiltered(array('model' => $this), Tool::create('created DESC', 1))->current();
//        return $status;
//    }
//
}