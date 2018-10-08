<?php
namespace Uni\Ui\Dialog;

use Tk\Request;

/**
 * This class uses the bootstrap dialog box model
 * @link http://getbootstrap.com/javascript/#modals
 *
 *
 * <code>
 * // doDefault()
 * $this->dialog = new \Uni\Ui\Dialog\PreEnrollment('Enroll Student');
 * $this->dialog->execute($request);
 *
 * ...
 * // show()
 * $template->insertTemplate('dialog', $this->dialog->show());
 * $template->setAttr('modelBtn', 'data-target', '#'.$this->dialog->getId());
 *
 * </code>
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PreEnrollment extends Iface
{

    /**
     * @var \Uni\Db\Subject
     */
    protected $subject = null;

    /**
     * @param $title
     */
    public function __construct($title)
    {
        parent::__construct($title);
        $this->addButton('Close');
        $this->addButton('Enroll', array('class' => 'btn btn-primary'));
    }

    /**
     * @return string
     */
    public function getEnrollButtonId()
    {
        return $this->getId().'-Enroll';
    }

    /**
     * Process the enrolments as submitted from the dialog
     *
     * @param Request $request
     * @throws \Exception
     */
    public function execute(Request $request)
    {
        if (!$request->has('enroll')) {
            return;
        }
        $config = \Uni\Config::getInstance();

        $this->subject = \Uni\Config::getInstance()->getSubject();
        if ($request->get('subjectId'))
            $this->subject = $config->getSubjectMapper()->find($request->get('subjectId'));

        if (!$this->subject)
            throw new \Tk\Exception('Invalid subject details');

        $list = array();

        // Check file list
        if ($request->getUploadedFile('csvFile') && $request->getUploadedFile('csvFile')->getError() == \UPLOAD_ERR_OK) {
            /* @var \Tk\UploadedFile $file */
            $file = $request->getUploadedFile('csvFile');
            if (($handle = fopen($file->getFile(), 'r')) !== FALSE) {
                $list = $this->processCsv($handle);
            }
        } else if($request->get('csvList')) {
            // Check textarea list
            $csvList = $request->get('csvList');
            if (($handle = fopen('data://text/plain,'.$csvList, 'r')) !== FALSE) {
                $list = $this->processCsv($handle);
            }
        }
        
        $error = array();
        $success = array();
        $info = array();
        foreach ($list as $i => $arr) {

            $uid = '';          // TODO: Make this the primary search param
            $username = '';     // TODO: Make this the secondary search param
            $email = '';        // TODO: We need to keep this for LTI integrations

            if (isset($arr['username']))
                $username = trim(strip_tags($arr['username']));
            if (isset($arr['uid']))
                $uid = trim(strip_tags($arr['uid']));
            if (isset($arr['email']))
                $email = trim(strip_tags($arr['email']));

            // Add users if found
            if (!$config->getSubjectMapper()->hasPreEnrollment($this->subject->getId(), $email)) {
                $config->getSubjectMapper()->addPreEnrollment($this->subject->getId(), $email, $uid, $username);
                $user = $config->getUserMapper()->findByEmail($email, $this->subject->institutionId);
                if ($user) {
                    $config->getSubjectMapper()->addUser($this->subject->getId(), $user->getId());
                }

                $success[] = $i . ' - Added ' . $email . ' to the subject enrollment list';
            } else {
                $info[] = $i . ' - User ' . $email . ' already enrolled, nothing done.';
            }
        }

        if (count($info)) {
            \Tk\Alert::addInfo(count($info) . ' records already enrolled and ignored.');
        }
        if (count($success)) {
            \Tk\Alert::addSuccess(count($success) . ' records successfully added to the enrollment list.');
        }
        if (count($error)) {
            \Tk\Alert::addError(count($error) . ' records contained errors.');
        }

        $request->getUri()->redirect();
    }


    /**
     * @param $stream
     * @return array
     */
    private function processCsv($stream)
    {
        $list = array();
        $row = 1;

        while (($data = fgetcsv($stream, 1000, ',')) !== FALSE) {
            $num = count($data);
            $list[$row] = array();
            for ($c=0; $c < $num; $c++) {
                if (filter_var($data[$c], FILTER_VALIDATE_EMAIL)) {
                    $list[$row]['email'] = $data[$c];
                } else if (preg_match('/^[0-9]+$/', $data[$c])) {
                    $list[$row]['uid'] = $data[$c];
                } else if (!preg_match('/.+@.+/', $data[$c])) {
                    $list[$row]['username'] = $data[$c];
                }
            }
            $row++;
        }
        fclose($stream);
        return $list;
    }


    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->addCss('dialog', 'tk-dialog-pre-enrollment');
        $this->setBody($this->makeBodyHtml());
        $this->getTemplate()->setAttr('dialog', 'data-enroll-btn', '#'.$this->getEnrollButtonId());
        $this->getTemplate()->setAttr('dialog', 'data-enroll-form', '#addEnrollmentForm');

        $js = <<<JS
jQuery(function($) {
  
  $('.tk-dialog-pre-enrollment').each(function () {
    var dialog = $(this);
    var enrollBtn = $(dialog.data('enroll-btn'));
    var enrollForm = $(dialog.data('enroll-form'));
    enrollBtn.on('click', function(e) {
      $('<input type="submit" name="enroll" value="Enroll" />').hide().appendTo(enrollForm).click().remove();
    });
  });
  
});
JS;
        $template->appendJs($js);
        
        return parent::show();
    }

    /**
     * DomTemplate magic method
     *
     * @return string
     */
    public function makeBodyHtml()
    {
        $url = htmlentities(\Uni\Config::getInstance()->getRequest()->getUri()->toString());
        $xhtml = <<<HTML
<form id="addEnrollmentForm" method="POST" action="$url" enctype="multipart/form-data">

  <div class="form-group form-group-sm">
    <label for="fid-csvFile" class="control-label">* Csv File:</label>
    <div>
    <input type="file" class="form-control tk-fileinput" id="fid-csvFile" name="csvFile"/>
    </div>
  </div>
  <p>OR</p>
  <div class="form-group form-group-sm">
    <label for="fid-csvList" class="control-label">* CSV List:</label>
    <textarea class="form-control" id="fid-csvList" name="csvList" style="height: 90px;"></textarea>
  </div>
  
  <p>Valid CSV formats are:</p>
  <p>Preferred Method:</p>
  <p><pre>
uid,email,username
123456,student1@uni.edu.au,student1
123457,staff2@uni.edu.au,staff1
</pre></p>

  <p><small>NOTE: The uid and username are currently optional. The email is the pimary value.</small></p>
    
</form>
HTML;
        return $xhtml;
    }
}
