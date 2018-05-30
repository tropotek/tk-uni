# Tk UNI Lib 

__Project:__ [ttek/tk-uni](http://packagist.org/packages/ttek/tk-uni)  
__Published:__ 19 Dec 2018
__Web:__ <http://www.tropotek.com/>  
__Authors:__ Michael Mifsud <http://www.tropotek.com/>  
  
## Contents

- [Installation](#installation)
- [Introduction](#introduction)

A base lib for all university institution sites

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

### Subject Routes

An example of staff subject routes setup in the `subject/routes.php`: 

```php
$params = array('role' => \Uni\Db\UserIface::ROLE_STAFF, 'subjectCode' = '');

$routes->add('staff-subject-dashboard',       new \Tk\Routing\Route('/staff/{subjectCode}/index.html', 'App\Controller\Staff\SubjectDashboard::doDefault', $params));
$routes->add('staff-subject-settings',        new \Tk\Routing\Route('/staff/{subjectCode}/settings.html', 'App\Controller\Subject\Edit::doDefault', $params));
$routes->add('staff-subject-edit',            new \Tk\Routing\Route('/staff/{subjectCode}/edit.html', 'App\Controller\Subject\Edit::doDefault', $params));
$routes->add('staff-subject-student-manager', new \Tk\Routing\Route('/staff/{subjectCode}/studentManager.html', 'App\Controller\User\StudentManager::doDefault', $params));
$routes->add('staff-subject-student-edit',    new \Tk\Routing\Route('/staff/{subjectCode}/studentEdit.html', 'App\Controller\User\StudentEdit::doDefault', $params));

```



