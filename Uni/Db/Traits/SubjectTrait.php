<?php
namespace Uni\Db\Traits;

use Uni\Config;
use Uni\Db\SubjectIface;
use Uni\Db\Subject;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait SubjectTrait
{


    /**
     * @var SubjectIface
     */
    private $_subject = null;



    /**
     * @return int
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * @param int $subjectId
     * @return SubjectTrait
     */
    public function setSubjectId($subjectId)
    {
        $this->subjectId = (int)$subjectId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Subject|null
     * @deprecated Use getSubjectObj()
     */
    public function getSubject()
    {
        return $this->getSubjectObj();
    }

    /**
     * Get the Subject object found using $this->subjectId
     *
     * @return Subject|SubjectIface|null
     */
    public function getSubjectObj()
    {
        if (!$this->_subject) {
            try {
                $this->_subject = Config::getInstance()->getSubjectMapper()->find($this->getSubjectId());
            } catch (\Exception $e) {}
        }
        return $this->_subject;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateSubjectId($errors = [])
    {
        if (!$this->getSubjectId()) {
            $errors['subjectId'] = 'Invalid value: subjectId';
        }
        return $errors;
    }

}