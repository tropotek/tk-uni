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
     * @var \Uni\Ui\Dialog\FindUser
     */
    protected $enrolledDialog = null;



    /**
     * @var \Uni\Table\PreEnrollment
     */
    protected $preTable = null;

    /**
     * @var \Uni\Ui\Dialog\PreEnrollment
     */
    protected $preDialog = null;



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


        $this->preDialog = new \Uni\Ui\Dialog\PreEnrollment('Pre-Enroll User');
        $this->preDialog->execute($request);

        $this->preTable = \Uni\Table\PreEnrollment::create()->init();
        $this->preTable->prependAction(\Tk\Table\Action\Link::createLink('Add Student', '#', 'fa fa-plus')
            ->setAttr('data-toggle', 'modal')
            ->setAttr('data-target', '#'.$this->preDialog->getId()));
        $list = $this->preTable->findList(array('subjectId' => $this->getSubject()->getId()));
        $this->preTable->setList($list);


        $this->enrolledTable = \Uni\Table\Enrolled::create()->init();
        $filter = array('subjectId' => $this->getSubject()->getId());
        $filter['type'] = array(\Uni\Db\Role::TYPE_STAFF, \Uni\Db\Role::TYPE_STUDENT);
        $this->enrolledTable->setList($this->enrolledTable->findList($filter));


        $filter = array();
        $filter['institutionId'] = $this->getSubject()->institutionId;
        $filter['active'] = '1';
        $filter['type'] = array(\Uni\Db\Role::TYPE_STUDENT, \Uni\Db\Role::TYPE_STAFF);
        $this->enrolledDialog = new \Uni\Ui\Dialog\FindUser('Enrol Student', $filter);
        $subject = $this->getSubject();
        $this->enrolledDialog->setOnSelect(function ($dialog, $data) use ($subject) {
            /** @var \Uni\Db\User $user */
            $user = \Uni\Config::getInstance()->getUserMapper()->findByHash($data['userHash'], $subject->institutionId);
            if (!$user || (!$user->isStaff() && !$user->isStudent())) {
                \Tk\Alert::addWarning('Invalid user.');
            } else {
                if (!$user->isEnrolled($subject->getId())) {
                    // TODO: test for any preconditions, maybe fire an enrollment event?
                    \Uni\Config::getInstance()->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                    \Tk\Alert::addSuccess($user->getName() . ' added to the subject ' . $subject->name);
                } else {
                    \Tk\Alert::addWarning($user->getName() . ' already enrolled in the subject ' . $subject->name);
                }
            }
        });
        $this->enrolledDialog->execute($request);

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
        $this->getActionPanel()->add(\Tk\Ui\Button::create('Enroll','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->enrolledDialog->getId())
            ->setAttr('title', 'Add an existing student to this subject');

        $this->getActionPanel()->add(\Tk\Ui\Button::create('Pre-Enroll','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->preDialog->getId())
            ->setAttr('title', 'Pre-Enroll a non-existing student, they will automatically be enrolled on login');

        $template = parent::show();



        // Enrolled Table
        $template->appendTemplate('enrolledTable', $this->enrolledTable->getRenderer()->show());
        // Enrolled Dialog
        $template->appendTemplate('enrollment', $this->enrolledDialog->show());


        // Pre Enrollment Table
        $template->appendTemplate('pendingTable', $this->preTable->getRenderer()->show());
        // Pre Enrolment Dialog
        $template->appendTemplate('enrollment', $this->preDialog->show());


        
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
  color: #efefef !important;
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

      <div class="tk-panel" data-panel-title="Enrolled" data-panel-icon="fa fa-users" var="pendingTable">
          <div class="small">
            <p>
              - Pre-enrolled users will automatically be enrolled into this subject on their next login.<br/>
              - Deleting an enrolled user from this list will also delete them from the pre-enrollment list.
            </p>
          </div>
      </div>

    </div>
  </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
    
}







