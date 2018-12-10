<?php
namespace Uni\Controller\Subject;

use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class EnrollmentManager extends \Uni\Controller\AdminIface
{


    /**
     * @var \Uni\Table\Enrolled
     */
    protected $enrolledTable = null;

    /**
     * @var \Uni\Table\PreEnrollment
     */
    protected $preEnrolTable = null;

    /**
     * @var \Uni\Ui\Dialog\PreEnrollment
     */
    protected $preEnrolDialog = null;

    /**
     * @var \Uni\Ui\Dialog\AjaxSelect
     */
    protected $enrolStudentDialog = null;

    /**
     * @var \Uni\Ui\Dialog\AjaxSelect
     */
    protected $enrolClassDialog = null;



    /**
     * EnrollmentManager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Subject Enrolments');
    }

    /**
     * @param \Tk\Request $request
     * @param string $subjectCode
     * @throws \Exception
     */
    public function doSubject(\Tk\Request $request, $subjectCode)
    {
        $this->doDefault($request);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        if (!$this->getSubject()) {
            throw new \Tk\Exception('Invalid subject details');
        }
        $this->setPageTitle("`" . $this->getSubject()->name . '` Enrolments');

        $subject = $this->getSubject();

        // Pre-Enroll Csv import dialog
        $this->preEnrolDialog = new \Uni\Ui\Dialog\PreEnrollment('Pre-Enroll User');
        $this->preEnrolDialog->execute($request);


        $filter = array();
        $filter['institutionId'] = $subject->institutionId;
        $filter['exclude'] = $subject->getId();

        $this->enrolClassDialog = new \Uni\Ui\Dialog\AjaxSelect('Enrol Class', \Uni\Uri::create('/ajax/subject/findFiltered.html'));
        $this->enrolClassDialog->setAjaxParams($filter);
        $this->enrolClassDialog->setNotes('Select the subject to enroll all the students into.');
        $this->enrolClassDialog->setOnSelect(function ($data) use ($subject) {
            /** @var \Uni\Db\Subject $destSubject */
            $config = \Uni\Config::getInstance();
            $destSubject = $config->getSubjectMapper()->find($data['selectedId']);
            $userList = $config->getUserMapper()->findFiltered(array(
                'subjectId' => $subject->getId()
            ));
            $i = 0;

            foreach ($userList as $user) {
                if (!$user->isEnrolled($destSubject->getId())) {
                    $config->getSubjectMapper()->addUser($destSubject->getId(), $user->getId());
                    $i++;
                }
            }
            if ($i) {
                \Tk\Alert::addSuccess('Added ' . $i . ' students to the subject `' . $destSubject->name . '`');
            }
            //return \Tk\Uri::create()->reset()->set('subjectId', $subject->getId());
            return \Uni\Uri::create()->reset();
        });
        $this->enrolClassDialog->execute($request);


        // Enrol A single student dialog
        $filter = array();
        $filter['institutionId'] = $this->getSubject()->institutionId;
        $filter['active'] = '1';
        $filter['type'] = array(\Uni\Db\Role::TYPE_STUDENT, \Uni\Db\Role::TYPE_COORDINATOR);

        $this->enrolStudentDialog = new \Uni\Ui\Dialog\AjaxSelect('Enrol Student', \Uni\Uri::create('/ajax/user/findFiltered.html'));
        $this->enrolStudentDialog->setAjaxParams($filter);
        //$this->enrolStudentDialog->setNotes('');
        $this->enrolStudentDialog->setOnSelect(function ($data) use ($subject) {
            /** @var \Uni\Db\User $user */
            $config = \Uni\Config::getInstance();
            $user = $config->getUserMapper()->findByHash($data['selectedId'], $subject->institutionId);
            if (!$user || (!$user->isStaff() && !$user->isStudent())) {
                \Tk\Alert::addWarning('Invalid user.');
            } else {
                if (!$user->isEnrolled($subject->getId())) {
                    $config->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                    \Tk\Alert::addSuccess($user->getName() . ' added to the subject ' . $subject->name);
                } else {
                    \Tk\Alert::addWarning($user->getName() . ' already enrolled in the subject ' . $subject->name);
                }
            }
            return \Uni\Uri::create()->reset();
        });
        $this->enrolStudentDialog->execute($request);


        // Enrolled Table
        $this->enrolledTable = \Uni\Table\Enrolled::create()->init();
        $filter = array('subjectId' => $this->getSubject()->getId());
        $filter['type'] = array(\Uni\Db\Role::TYPE_COORDINATOR, \Uni\Db\Role::TYPE_STUDENT);
        $this->enrolledTable->setList($this->enrolledTable->findList($filter));

        // Pre-Enrol table
        $this->preEnrolTable = \Uni\Table\PreEnrollment::create()->init();
        $this->preEnrolTable->prependAction(\Tk\Table\Action\Link::createLink('Pre-Enrol', '#', 'fa fa-plus')
            ->setAttr('data-toggle', 'modal')
            ->setAttr('data-target', '#'.$this->preEnrolDialog->getId()));
        $list = $this->preEnrolTable->findList(array('subjectId' => $this->getSubject()->getId()));
        $this->preEnrolTable->setList($list);

    }

    /**
     * @return null|\Uni\Db\Subject|\Uni\Db\SubjectIface
     */
    public function getSubject()
    {
        return $this->getConfig()->getSubject();
    }


    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Enrol','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->enrolStudentDialog->getId())
            ->setAttr('title', 'Add an existing student to this subject');

        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Pre-Enrol','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->preEnrolDialog->getId())
            ->setAttr('title', 'Pre-Enrol a non-existing student, they will automatically be enrolled on login');

        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Enrol Into...', '#', 'fa fa-copy'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->enrolClassDialog->getId())
            ->setAttr('title', 'Copy this enrollment list into another subject.');

        $template = parent::show();


        // Enrolled Table
        $template->appendTemplate('enrolledTable', $this->enrolledTable->getRenderer()->show());
        // Pre Enrollment Table
        $template->appendTemplate('pendingTable', $this->preEnrolTable->getRenderer()->show());


        // Enrolled Dialog
        $template->appendTemplate('enrollment', $this->enrolStudentDialog->show());

        // Pre Enrolment Dialog
        $template->appendTemplate('enrollment', $this->preEnrolDialog->show());

        // Enrolment Copy Dialog
        $template->appendTemplate('enrollment', $this->enrolClassDialog->show());


        
        $js = <<<JS
jQuery(function($) {
  
  $('tr[data-user-id]').hover(
    function(e) {
      var userId = $(this).attr('data-user-id');
      $('tr[data-user-id="'+userId+'"]').addClass('tk-hover');
    },
    function(e) {
      var userId = $(this).attr('data-user-id');
      $('tr[data-user-id="'+userId+'"]').removeClass('tk-hover');
    }
  );
  
});
JS;
        $template->appendJs($js);

        $css = <<<CSS
.tk-table .tk-pending-users tr.enrolled td {
  color: #999;
}
.tk-table tr.tk-hover td {
  background-color: #7796b4;
}
CSS;
        $template->appendCss($css);

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div var="enrollment">

  <div class="row">
    <div class="col-md-8">

      <div class="tk-panel" data-panel-title="Enrolled" data-panel-icon="fa fa-users" var="enrolledTable"></div>

    </div>
    <div class="col-md-4">
    
      <div class="tk-panel" data-panel-title="Pre-Enrollment" data-panel-icon="fa fa-users" var="pendingTable">
          <div>
            <p>
              Pre-enrolled users will automatically be enrolled into this subject on their next login.
            </p>
            <p class="small">Note: Deleting a user from this list will <b>not</b> delete them from the `Enrolled` list.</p>
          </div>
      </div>

    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
    
}







