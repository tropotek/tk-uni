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
     * @throws \Exception
     */
    public function doFindFiltered(Request $request)
    {
        $config = \Uni\Config::getInstance();
        $status = 200;  // change this on error
        $filter = $request->all();
        $list = array();
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
        try {
            $list = $config->getSubjectMapper()->findFiltered($filter)->toArray();
//        $data = array();
//        foreach ($list as $subject) {
//            $data[] = $subject;
//        }
        } catch (\Exception $e) {
            $status = \Tk\ResponseJson::HTTP_INTERNAL_SERVER_ERROR;
            $list = array('error' => $e->getMessage());
        }
        return \Tk\ResponseJson::createJson($list, $status);
    }

}