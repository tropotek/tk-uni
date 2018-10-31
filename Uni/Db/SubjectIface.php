<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface SubjectIface extends \Tk\Db\ModelInterface, \Tk\ValidInterface
{


    /**
     * @return int
     */
    public function getInstitutionId();

    /**
     * @return InstitutionIface
     */
    public function getInstitution();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return \DateTime
     */
    public function getDateStart();

    /**
     * @return \DateTime
     */
    public function getDateEnd();

}