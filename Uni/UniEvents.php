<?php
namespace Uni;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class UniEvents
{


    /**
     * This event is called when a user is migrated from one course to another.
     * In this event all student course data from the source course should be moved
     * to the destination course.
     *
     * Event Data:
     *  'subjectFromId', 'subjectToId', 'userId
     *
     * @event \Tk\Event\Event
     */
    const SUBJECT_MIGRATE_USER = 'subject.migrate.user';



}