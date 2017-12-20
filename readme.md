# Tk UNI Lib 

__Project:__ [ttek/tk-uni](http://packagist.org/packages/ttek/tk-uni)  
__Published:__ 19 Dec 2018
__Web:__ <http://www.tropotek.com/>  
__Authors:__ Michael Mifsud <http://www.tropotek.com/>  
  
## Contents

- [Installation](#installation)
- [Introduction](#introduction)


## Installation

Available on Packagist ([ttek/tk-form](http://packagist.org/packages/ttek/tk-uni))
and as such installable via [Composer](http://getcomposer.org/).

```bash
composer require ttek/tk-uni
```

Or add the following to your composer.json file:

```json
"ttek/tk-uni": "~2.0"
```

If you do not use Composer, you can grab the code from GitHub, and use any
PSR-0 compatible autoloader (e.g. the [tk-uni](https://github.com/tropotek/tk-uni))
to load the classes.

## Introduction

### Course Routes

An example of staff course routes setup in the `config/routes.php`: 

```php
$params = array('role' => \App\Db\UserGroup::ROLE_STAFF, 'courseCode' = '');

$routes->add('staff-c-dashboard',       new \Tk\Routing\Route('/staff/{courseCode}/index.html', 'App\Controller\Staff\CourseDashboard::doDefault', $params));
$routes->add('staff-c-settings',        new \Tk\Routing\Route('/staff/{courseCode}/settings.html', 'App\Controller\Course\Edit::doDefault', $params));
$routes->add('staff-c-edit',            new \Tk\Routing\Route('/staff/{courseCode}/edit.html', 'App\Controller\Course\Edit::doDefault', $params));
$routes->add('staff-c-student-manager', new \Tk\Routing\Route('/staff/{courseCode}/studentManager.html', 'App\Controller\User\StudentManager::doDefault', $params));
$routes->add('staff-c-student-edit',    new \Tk\Routing\Route('/staff/{courseCode}/studentEdit.html', 'App\Controller\User\StudentEdit::doDefault', $params));

```









