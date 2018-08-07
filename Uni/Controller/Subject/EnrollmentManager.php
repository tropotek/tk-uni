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
     * @var \Uni\Ui\Dialog\FindUser
     */
    protected $userDialog = null;

    /**
     * @var \Uni\Table\PreEnrollment
     */
    protected $preEnrollmentTable = null;

    /**
     * @var \Uni\Ui\Table\Enrolled
     */
    protected $enrolledTable = null;



    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doSubject(Request $request, $subjectCode)
    {
        $this->subject = $this->getConfig()->getSubjectMapper()->findByCode($subjectCode, $this->getConfig()->getInstitutionId());
        $this->doDefault($request);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->subject = $this->getConfig()->getSubject();
        if (!$this->subject) {
            $this->subject = $this->getConfig()->getSubjectMapper()->find($request->get('subjectId'));
        }
        if (!$this->subject)
            throw new \Tk\Exception('Invalid subject details');
        
        $this->setPageTitle("`" . $this->subject->name . '` Enrolments');

        $this->enrolledTable = new \Uni\Ui\Table\Enrolled($this->subject);
        $this->preEnrollmentTable = new \Uni\Ui\Table\PreEnrollment($this->subject);

        $filter = array();
        $filter['institutionId'] = $this->subject->institutionId;
        $filter['active'] = '1';
        $filter['type'] = array(\Uni\Db\Role::TYPE_STUDENT, \Uni\Db\Role::TYPE_STAFF);
        $this->userDialog = new \Uni\Ui\Dialog\FindUser('Enrol Student', $filter);
        $subject = $this->subject;
        $this->userDialog->setOnSelect(function ($dialog, $data) use ($subject) {
            /** @var \Uni\Db\User $user */
            $user = $this->getConfig()->getUserMapper()->findByHash($data['userHash'], $subject->institutionId);
            if (!$user || (!$user->isStaff() && !$user->isStudent())) {
                \Tk\Alert::addWarning('Invalid user.');
            } else {
                if (!$user->isEnrolled($subject->getId())) {
                    // TODO: test for any preconditions, maybe fire an enrollment event?
                    $this->getConfig()->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                    \Tk\Alert::addSuccess($user->getName() . ' added to the subject ' . $subject->name);
                } else {
                    \Tk\Alert::addWarning($user->getName() . ' already enrolled in the subject ' . $subject->name);
                }
            }
        });
        $this->userDialog->execute($request);

    }


    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        // Enrolment Dialog
        $template->appendTemplate('enrollment', $this->userDialog->show());

        //$template->setAttr('addUser', 'data-target', '#'.$this->userDialog->getId());
        $this->getActionPanel()->add(\Tk\Ui\Button::create('Enroll','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->userDialog->getId())
            ->setAttr('title', 'Add an existing student to this subject');

        // Enrolled Table
        $template->appendTemplate('enrolledTable', $this->enrolledTable->show());
        
        // Pending Table
        $template->appendTemplate('pendingTable', $this->preEnrollmentTable->show());

        //$template->setAttr('modelBtn', 'data-target', '#'.$this->pendingTable->getDialog()->getId());
        $this->getActionPanel()->add(\Tk\Ui\Button::create('Pre-Enroll','#', 'fa fa-user-plus'))
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->preEnrollmentTable->getDialog()->getId())
            ->setAttr('title', 'Pre-Enroll a non-existing student, they will automatically be enrolled on login');
        
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
.tk-table tr.tk-hover td {
  background-color: #7796b4;
  color: #efefef !important;
}
CSS;
        $template->appendCss($css);

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div var="enrollment">

  <div class="row">
    <div class="col-md-8">

      <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-users"></i> <span var="">Enrolled</span></div>
        <div class="panel-body">
          <div var="enrolledTable"></div>
        </div>
      </div>

    </div>
    <div class="col-md-4">

      <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-users"></i> <span>Pending</span></div>
        <div class="panel-body">
          <div var="pendingTable"></div>
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

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
    
}







