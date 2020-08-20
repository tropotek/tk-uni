<?php
namespace Uni\Db\Traits;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
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

}