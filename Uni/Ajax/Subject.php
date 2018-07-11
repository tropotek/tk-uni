<?php
namespace Uni\Ajax;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Subject
{

    /**
     * @param Request $request
     * @return \Tk\Response
     * @throws \Tk\Db\Exception
     */
    public function doFindFiltered(Request $request)
    {
        $status = 200;  // change this on error
        $filter = $request->all();
        if (!empty($filter['subjectId'])) {
            $filter['exclude'] = $filter['subjectId'];
            unset($filter['subjectId']);
        }
        if (empty($filter['keywords'])) {
            unset($filter['keywords']);
        }
        if (!empty($filter['ignoreUser']) && !empty($filter['userId'])) {
            unset($filter['userId']);
        }

        $list = \Uni\Db\SubjectMap::create()->findFiltered($filter);
        $data = array();
        
        foreach ($list as $subject) {
            $data[] = $subject;
        }
        return \Tk\ResponseJson::createJson($data, $status);
    }

}