<?php
namespace Uni\Ajax;

use Tk\Request;
/**
 * Class Index
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class User
{

    /**
     * @param Request $request
     * @return \Tk\Response
     * @throws \Tk\Db\Exception
     */
    public function doFindFiltered(Request $request)
    {
        $status = 200;  // change this on error
        $config = \Uni\Config::getInstance();
        
        $users = array();
        $filter = $request->all();
        unset($filter['subjectId']);
        
        if (!empty($filter['keywords'])) {
            if ($filter['keywords'][0] == '*') {    // Keep wildcard char as an undocumented feature for now
                $filter['keywords'] = '';
            }
            $users = $config->getSubjectMapper()->findFiltered($filter, \Tk\Db\Tool::create('a.name', 25))->toArray();
            foreach ($users as $user) {
                $user->id = '';
                $user->username = '';
                $user->password = '';
            }
        }
        return \Tk\ResponseJson::createJson($users, $status);
    }

}