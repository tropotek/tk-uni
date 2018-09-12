<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface InstitutionIface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getUserId();

    /**
     * @return UserIface
     */
    public function getUser();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return string
     */
    public function getHash();

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @return \Tk\Uri
     */
    public function getLoginUrl();

    /**
     * @param string $subjectCode
     * @return SubjectIface
     * @todo: \App\Db\SubjectMap::create()->findByCode($subjectCode, $this->getId());
     */
    public function findSubjectByCode($subjectCode);

    /**
     * @param int $subjectId
     * @return SubjectIface
     * @todo: \App\Db\SubjectMap::create()->find($subjectId);
     */
    public function findSubject($subjectId);

}