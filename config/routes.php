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

$config = \Uni\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;



// Public Pages
$routes->add('login', new \Tk\Routing\Route('/login.html', 'Uni\Controller\Login::doDefault'));
$routes->add('institution-login', new \Tk\Routing\Route('/inst/{instHash}/login.html', 'Uni\Controller\Login::doInsLogin'));
$routes->add('recover', new \Tk\Routing\Route('/recover.html', 'Uni\Controller\Recover::doDefault'));
$routes->add('register', new \Tk\Routing\Route('/register.html', 'Uni\Controller\Register::doDefault'));
$routes->add('logout', new \Tk\Routing\Route('/logout.html', 'Uni\Controller\Logout::doDefault'));
$routes->add('institution-list', new \Tk\Routing\Route('/institutions.html', 'Uni\Controller\Institution\Listing::doDefault'));



// Admin Pages
//$routes->add('admin-dashboard', new \Tk\Routing\Route('/admin/index.html', 'Uni\Controller\Admin\Dashboard::doDefault'));
//$routes->add('admin-dashboard-base', new \Tk\Routing\Route('/admin/', 'Uni\Controller\Admin\Dashboard::doDefault'));

$routes->add('admin-institution-plugin-manager', new \Tk\Routing\Route('/admin/{zoneName}/{zoneId}/plugins.html', 'Uni\Controller\PluginZoneManager::doDefault',
    array('zoneName' => 'institution', 'zoneId' => '0') ));

$routes->add('admin-settings', new \Tk\Routing\Route('/admin/settings.html', 'Uni\Controller\Admin\Settings::doDefault'));

$routes->add('admin-institution-manager', new \Tk\Routing\Route('/admin/institutionManager.html', 'Uni\Controller\Institution\Manager::doDefault'));
$routes->add('admin-institution-edit', new \Tk\Routing\Route('/admin/institutionEdit.html', 'Uni\Controller\Institution\Edit::doDefault'));

$routes->add('admin-adminUser-manager', new \Tk\Routing\Route('/admin/adminManager.html', 'Uni\Controller\User\Manager::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_ADMIN)));
$routes->add('admin-adminUser-edit', new \Tk\Routing\Route('/admin/adminEdit.html', 'Uni\Controller\User\Edit::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_ADMIN)));

$routes->add('admin-user-profile', new \Tk\Routing\Route('/admin/profile.html', 'Uni\Controller\User\Profile::doDefault'));

$routes->add('admin-dev-events', new \Tk\Routing\Route('/admin/dev/events.html', 'Bs\Controller\Admin\Dev\Events::doDefault'));



// Client Pages
//$routes->add('client-dashboard', new \Tk\Routing\Route('/client/index.html', 'Uni\Controller\Client\Dashboard::doDefault'));
//$routes->add('client-dashboard-base', new \Tk\Routing\Route('/client/', 'Uni\Controller\Client\Dashboard::doDefault'));

$routes->add('client-institution-plugin-manager', new \Tk\Routing\Route('/client/{zoneName}/{zoneId}/plugins.html', 'Uni\Controller\PluginZoneManager::doDefault',
    array('zoneName' => 'institution', 'zoneId' => '0') ));

$routes->add('client-institution-edit', new \Tk\Routing\Route('/client/settings.html', 'Uni\Controller\Institution\Edit::doDefault'));
$routes->add('client-user-profile', new \Tk\Routing\Route('/client/profile.html', 'Uni\Controller\User\Profile::doDefault'));

$routes->add('client-role-manager', new \Tk\Routing\Route('/client/roleManager.html', 'Uni\Controller\Role\Manager::doDefault'));
$routes->add('client-role-edit', new \Tk\Routing\Route('/client/roleEdit.html', 'Uni\Controller\Role\Edit::doDefault'));

$routes->add('client-staff-manager', new \Tk\Routing\Route('/client/staffManager.html', 'Uni\Controller\User\Manager::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_STAFF)));
$routes->add('client-staff-edit', new \Tk\Routing\Route('/client/staffEdit.html', 'Uni\Controller\User\Edit::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_STAFF)));

$routes->add('client-student-manager', new \Tk\Routing\Route('/client/studentManager.html', 'Uni\Controller\User\Manager::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_STUDENT)));
$routes->add('client-student-edit', new \Tk\Routing\Route('/client/studentEdit.html', 'Uni\Controller\User\Edit::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_STUDENT)));

$routes->add('client-subject-manager', new \Tk\Routing\Route('/client/subjectManager.html', 'Uni\Controller\Subject\Manager::doDefault'));
$routes->add('client-subject-edit', new \Tk\Routing\Route('/client/subjectEdit.html', 'Uni\Controller\Subject\Edit::doDefault'));
$routes->add('client-subject-enrollment', new \Tk\Routing\Route('/client/subjectEnrollment.html', 'Uni\Controller\Subject\EnrollmentManager::doDefault'));



// Staff Pages
//$routes->add('staff-dashboard', new \Tk\Routing\Route('/staff/index.html', 'Uni\Controller\Staff\Dashboard::doDefault'));
//$routes->add('staff-dashboard-base', new \Tk\Routing\Route('/staff/', 'Uni\Controller\Staff\Dashboard::doDefault'));
//$routes->add('staff-subject-dashboard', new \Tk\Routing\Route('/staff/{subjectCode}/index.html', 'Uni\Controller\Staff\SubjectDashboard::doDefault'));

$routes->add('staff-user-profile', new \Tk\Routing\Route('/staff/profile.html', 'Uni\Controller\User\Profile::doDefault'));

$routes->add('staff-subject-manager', new \Tk\Routing\Route('/staff/subjectManager.html', 'Uni\Controller\Subject\StudentManager::doDefault'));

$routes->add('staff-subject-add', new \Tk\Routing\Route('/staff/subjectEdit.html', 'Uni\Controller\Subject\Edit::doDefault'));
$routes->add('staff-subject-add-enrollment', new \Tk\Routing\Route('/staff/subjectEnrollment.html', 'Uni\Controller\Subject\EnrollmentManager::doDefault'));

$routes->add('staff-subject-edit', new \Tk\Routing\Route('/staff/{subjectCode}/subjectEdit.html', 'Uni\Controller\Subject\Edit::doSubject'));
$routes->add('staff-subject-enrollment', new \Tk\Routing\Route('/staff/{subjectCode}/subjectEnrollment.html', 'Uni\Controller\Subject\EnrollmentManager::doSubject'));

$routes->add('staff-role-manager', new \Tk\Routing\Route('/staff/roleManager.html', 'Uni\Controller\Role\Manager::doDefault'));
$routes->add('staff-role-edit', new \Tk\Routing\Route('/staff/roleEdit.html', 'Uni\Controller\Role\Edit::doDefault'));

$routes->add('staff-staff-manager', new \Tk\Routing\Route('/staff/staffManager.html', 'Uni\Controller\User\Manager::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_STAFF)));
$routes->add('staff-staff-edit', new \Tk\Routing\Route('/staff/staffEdit.html', 'Uni\Controller\User\Edit::doDefaultRole',
    array('targetRole' => \Uni\Db\Role::TYPE_STAFF)));

$routes->add('staff-student-manager', new \Tk\Routing\Route('/staff/{subjectCode}/studentManager.html', 'Uni\Controller\User\Manager::doSubject',
    array('targetRole' => \Uni\Db\Role::TYPE_STUDENT)));
$routes->add('staff-student-edit', new \Tk\Routing\Route('/staff/{subjectCode}/studentEdit.html', 'Uni\Controller\User\Edit::doSubject',
    array('targetRole' => \Uni\Db\Role::TYPE_STUDENT)));

$routes->add('staff-subject-plugin-manager', new \Tk\Routing\Route('/staff/{zoneName}/{zoneId}/plugins.html', 'Uni\Controller\PluginZoneManager::doDefault',
    array('zoneName' => 'subject', 'zoneId' => '0') ));



// Student Pages
//$routes->add('student-dashboard', new \Tk\Routing\Route('/student/index.html', 'Uni\Controller\Student\Dashboard::doDefault'));
//$routes->add('student-dashboard-base', new \Tk\Routing\Route('/student/', 'Uni\Controller\Student\Dashboard::doDefault'));
//$routes->add('student-subject-dashboard', new \Tk\Routing\Route('/student/{subjectCode}/index.html', 'Uni\Controller\Student\SubjectDashboard::doDefault'));

$routes->add('student-user-profile', new \Tk\Routing\Route('/student/profile.html', 'Uni\Controller\User\Profile::doDefault'));
//$routes->add('student-subject-manager', new \Tk\Routing\Route('/student/subjectManager.html', 'Uni\Controller\Subject\StudentManager::doDefault'));





// Ajax Urls
$routes->add('ajax-user-findFiltered', new \Tk\Routing\Route('/ajax/user/findFiltered.html', 'Uni\Ajax\User::doFindFiltered'));
$routes->add('ajax-subject-findFiltered', new \Tk\Routing\Route('/ajax/subject/findFiltered.html', 'Uni\Ajax\Subject::doFindFiltered'));



