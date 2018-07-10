<?php

/* 
 * NOTE: Be sure to add routes in correct order as the first match will win
 * 
 * Route Structure
 * $route = new Route(
 *     '/archive/{month}',              // path
 *     '\Namespace\Class::method',      // Callable or class::method string
 *     array('month' => 'Jan'),         // Params and defaults to path params... all will be sent to the request object.
 *     array('GET', 'POST', 'HEAD')     // methods
 * );
 */
$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;


// Default Home catchall
$params = array();

$routes->add('login', new \Tk\Routing\Route('/login.html', 'Uni\Controller\Login::doDefault', $params));
$routes->add('institution-login', new \Tk\Routing\Route('/inst/{instHash}/login.html', 'Uni\Controller\Login::doInsLogin', $params));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'Uni\Controller\Logout::doDefault', $params));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'Uni\Controller\Recover::doDefault', $params));
$routes->add('register', new \Tk\Routing\Route('/register.html', 'Uni\Controller\Register::doDefault', $params));


// Admin Pages
$params = array('role' => \Uni\Db\User::ROLE_ADMIN);
$routes->add('admin-dashboard', new \Tk\Routing\Route('/admin/index.html', 'Uni\Controller\Admin\Dashboard::doDefault', $params));
$routes->add('admin-dashboard-base', new \Tk\Routing\Route('/admin/', 'Uni\Controller\Admin\Dashboard::doDefault', $params));

$routes->add('admin-settings', new \Tk\Routing\Route('/admin/settings.html', 'Uni\Controller\Admin\Settings::doDefault', $params));
$routes->add('admin-institution-manager', new \Tk\Routing\Route('/admin/institutionManager.html', 'Uni\Controller\Institution\Manager::doDefault', $params));
$routes->add('admin-institution-edit', new \Tk\Routing\Route('/admin/institutionEdit.html', 'Uni\Controller\Institution\Edit::doDefault', $params));
$routes->add('admin-institution-plugin-manager', new \Tk\Routing\Route('/admin/{zoneName}/{zoneId}/plugins.html', 'Uni\Controller\PluginZoneManager::doDefault',
    array('role' => \Uni\Db\User::ROLE_ADMIN, 'zoneName' => 'institution', 'zoneId' => '0') ));
$routes->add('admin-user-manager', new \Tk\Routing\Route('/admin/userManager.html', 'Uni\Controller\User\Manager::doDefault', $params));
$routes->add('admin-user-edit', new \Tk\Routing\Route('/admin/userEdit.html', 'Uni\Controller\User\Edit::doDefault', $params));
$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'Uni\Controller\User\Profile::doDefault', $params));



// Client Pages
$params = array('role' => \Uni\Db\User::ROLE_CLIENT);
//$routes->add('client-dashboard', new \Tk\Routing\Route('/client/index.html', 'Uni\Controller\Client\Dashboard::doDefault', $params));
//$routes->add('client-dashboard-base', new \Tk\Routing\Route('/client/', 'Uni\Controller\Client\Dashboard::doDefault', $params));

$routes->add('client-user-profile', new \Tk\Routing\Route('/client/profile.html', 'Uni\Controller\User\Profile::doDefault', $params));
$routes->add('client-staff-manager', new \Tk\Routing\Route('/client/staffManager.html', 'Uni\Controller\User\StaffManager::doDefault', $params));
$routes->add('client-staff-edit', new \Tk\Routing\Route('/client/staffEdit.html', 'Uni\Controller\User\StaffEdit::doDefault', $params));
$routes->add('client-student-manager', new \Tk\Routing\Route('/client/studentManager.html', 'Uni\Controller\User\StudentManager::doDefault', $params));
$routes->add('client-student-edit', new \Tk\Routing\Route('/client/studentEdit.html', 'Uni\Controller\User\StudentEdit::doDefault', $params));

$routes->add('client-institution-edit', new \Tk\Routing\Route('/client/institutionEdit.html', 'Uni\Controller\Institution\Edit::doDefault', $params));
$routes->add('client-institution-plugin-manager', new \Tk\Routing\Route('/client/{zoneName}/{zoneId}/plugins.html', 'Uni\Controller\PluginZoneManager::doDefault',
    array('role' => \Uni\Db\User::ROLE_CLIENT, 'zoneName' => 'institution', 'zoneId' => '0') ));

$routes->add('client-subject-manager', new \Tk\Routing\Route('/client/subjectManager.html', 'Uni\Controller\Subject\Manager::doDefault', $params));
$routes->add('client-subject-edit', new \Tk\Routing\Route('/client/subjectEdit.html', 'Uni\Controller\Subject\Edit::doDefault', $params));
$routes->add('client-subject-enrollment', new \Tk\Routing\Route('/client/subjectEnrollment.html', 'Uni\Controller\Subject\EnrollmentManager::doDefault', $params));




// Staff Pages
$params = array('role' => \Uni\Db\User::ROLE_STAFF);
//$routes->add('staff-dashboard', new \Tk\Routing\Route('/staff/index.html', 'Uni\Controller\Staff\Dashboard::doDefault', $params));
//$routes->add('staff-dashboard-base', new \Tk\Routing\Route('/staff/', 'Uni\Controller\Staff\Dashboard::doDefault', $params));

$routes->add('staff-subject-manager', new \Tk\Routing\Route('/staff/subjectManager.html', 'Uni\Controller\Subject\Manager::doDefault', $params));
$routes->add('staff-subject-edit', new \Tk\Routing\Route('/staff/subjectEdit.html', 'Uni\Controller\Subject\Edit::doDefault', $params));
$routes->add('staff-subject-enrollment', new \Tk\Routing\Route('/staff/subjectEnrollment.html', 'Uni\Controller\Subject\EnrollmentManager::doDefault', $params));


$routes->add('staff-student-manager', new \Tk\Routing\Route('/staff/studentManager.html', 'Uni\Controller\User\StudentManager::doDefault', $params));
$routes->add('staff-student-edit', new \Tk\Routing\Route('/staff/studentEdit.html', 'Uni\Controller\User\StudentEdit::doDefault', $params));
$routes->add('staff-user-profile', new \Tk\Routing\Route('/staff/profile.html', 'Uni\Controller\User\Profile::doDefault', $params));


//$routes->add('staff-user-manager', new \Tk\Routing\Route('/staff/userManager.html', 'Uni\Controller\User\Manager::doDefault', $params));
//$routes->add('staff-user-edit', new \Tk\Routing\Route('/staff/userEdit.html', 'Uni\Controller\User\Edit::doDefault', $params));
//$routes->add('staff-user-profile', new \Tk\Routing\Route('/staff/profile.html', 'Uni\Controller\User\Profile::doDefault', $params));



// Student Pages
$params = array('role' => \Uni\Db\User::ROLE_STUDENT);
//$routes->add('student-dashboard', new \Tk\Routing\Route('/student/index.html', 'Uni\Controller\Student\Dashboard::doDefault', $params));
//$routes->add('student-dashboard-base', new \Tk\Routing\Route('/student/', 'Uni\Controller\Student\Dashboard::doDefault', $params));

$routes->add('student-user-profile', new \Tk\Routing\Route('/student/profile.html', 'Uni\Controller\User\Profile::doDefault', $params));


// Ajax Urls
$params = array('role' => array(\Uni\Db\User::ROLE_ADMIN, \Uni\Db\User::ROLE_CLIENT, \Uni\Db\User::ROLE_STAFF, \Uni\Db\User::ROLE_STUDENT));
$routes->add('ajax-user-findFiltered', new \Tk\Routing\Route('/ajax/user/findFiltered.html', 'Uni\Ajax\User::doFindFiltered', $params));
$routes->add('ajax-subject-findFiltered', new \Tk\Routing\Route('/ajax/subject/findFiltered.html', 'Uni\Ajax\Subject::doFindFiltered', $params));



