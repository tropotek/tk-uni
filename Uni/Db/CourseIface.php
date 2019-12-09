<?php
namespace Uni\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
interface CourseIface extends \Tk\Db\ModelInterface, \Tk\ValidInterface
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
     * @return int
     */
    public function getCoordinatorId();

    /**
     * @return null|User
     */
    public function getCoordinator();

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
     * @return string
     */
    public function getEmailSignature();

    /**
     * @return string
     */
    public function getDescription();


}