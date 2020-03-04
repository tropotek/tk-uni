<?php
namespace Uni\Controller\Mentor;

use Tk\Form;
use Tk\Request;
use Dom\Template;
use \Tk\Form\Field;
use \Tk\Form\Event;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Import extends \Uni\Controller\AdminEditIface
{


    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Mentor Import');
        $this->getConfig()->unsetSubject();

    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {

        $this->setForm($this->getConfig()->createForm('formEdit'));
        $this->getForm()->setRenderer($this->getConfig()->createFormRenderer($this->getForm()));

        $this->getForm()->appendField(Field\File::create('csvFile'))->addCss('tk-fileinput')->setTabGroup('File');
        $this->getForm()->appendField(Field\Textarea::create('csvText'))->setTabGroup('Text');

        $this->getForm()->appendField(Field\Checkbox::create('clear')->setCheckboxLabel('Clear existing mentor lists'));

        $this->getForm()->appendField(Event\Submit::create('import', array($this, 'doSubmit'))->setIcon('fa fa-download'));

        //$this->getForm()->load($this->data->toArray());
        $this->getForm()->execute();


    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        $values = $form->getValues();

        /** @var Field\File $file */
        $file = $form->getField('csvFile');
        $csv = '';
        if ($file && $file->hasFile()) {

            $csv = file_get_contents($file->getUploadedFile()->getRealPath());
        } else if (!empty($values['csvText'])) {
            $csv = $values['csvText'];
        }

        if (!$csv) {
            $form->addFieldError('csvFile', 'Please upload a valid CSV file.');
            $form->addFieldError('csvText', 'Please past valid CSV text.');
        }

        if ($form->hasErrors()) {
            return;
        }

        if ($form->getFieldValue('clear')) {
            $this->getDb()->quote('TRUNCATE user_mentor');
            \Tk\Log::info('Clearing existing mentor list');
        }

        $results = $this->executeCsv($csv);

        \Tk\Alert::addInfo('Mentor CSV list imported: Success['.count($results['success']).'] Fail['.count($results['fail']).']');

        //$this->getSession()->set('csvMentorImportResult', $results);
        //vd($results['error'], $results['remCsv']);
        if (count($results['error'])) {
            $err = trim(implode("<br/>\n", $results['error']), "<br/>\n");
            \Tk\Alert::addWarning('Import Error: <br/>' . $err . '<br/>Remaining CSV: <br/><pre>'.$results['remCsv'].'</pre>');
        }

        $event->setRedirect(\Tk\Uri::create());
    }

    /**
     * @param string $csv
     * @return array|null
     * @throws \Exception
     */
    public function executeCsv($csv)
    {
        $csv = trim($csv);
        if (!$csv) throw new \Tk\Exception('Empty CSV string');
        try {

            $success = array();
            $fail = array();
            $error = array();
            $rowId = -1;

            $temp = fopen('php://temp', 'r+');
            fputs($temp, $csv);
            rewind($temp);

            while ($csvRow = fgetcsv($temp)) {
                $row = array_map('trim', $csvRow);
                if (strtolower($row[0]) == 'mentorid' || strtolower($csvRow[1]) == 'studentid') continue;       // ignore header row
                $rowId++;
                if (!trim(implode('', $row))) continue;


                $staff = null;
                if ((preg_match('/^[0-9]{4,10}$/', $row[0]))) {
                    $staff = $this->getConfig()->getUserMapper()->findByUid($row[0], $this->getConfig()->getInstitutionId());
                } else if (filter_var($row[0], \FILTER_VALIDATE_EMAIL)) {
                    $staff = $this->getConfig()->getUserMapper()->findByEmail($row[0], $this->getConfig()->getInstitutionId());
                } else {
                    $staff = $this->getConfig()->getUserMapper()->findByUsername($row[0], $this->getConfig()->getInstitutionId());
                }
                if ($staff && !$staff->isMentor()) {
                    $staff = null;
                }
                if (!$staff) {
                    $error[md5($row[0])] = 'Staff not found: ' . $row[0];
                }

                $student = null;
                if ((preg_match('/^[0-9]{4,10}$/', $row[1]))) {
                    $student = $this->getConfig()->getUserMapper()->findByUid($row[1], $this->getConfig()->getInstitutionId());
                } else if (filter_var($row[1], \FILTER_VALIDATE_EMAIL)) {
                    $student = $this->getConfig()->getUserMapper()->findByEmail($row[1], $this->getConfig()->getInstitutionId());
                } else {
                    $student = $this->getConfig()->getUserMapper()->findByUsername($row[1], $this->getConfig()->getInstitutionId());
                }
                if ($student && !$student->isStudent()) {
                    $student = null;
                }
                if (!$student) {
                    $error[md5($row[1])] = 'Student not found: ' . $row[1];
                }

                if (!$staff || !$student) {
                    $fail[$rowId] = $row;
                    continue;
                }

                $this->getConfig()->getUserMapper()->addMentor($staff->getId(), $student->getId());
                $success[$rowId] = $row;

            }
            fclose($temp);

            $remCsv = '';
            foreach ($fail as $row) {
                $remCsv .= implode(',', $row) . "\n";
            }
            return array('success' => $success, 'fail' => $fail, 'error' => $error, 'csv' => $csv, 'remCsv' => $remCsv, 'total' => $rowId+1);
        } catch (\Exception $e) {
            \Tk\Log::error($e->__toString());
        }
    }

    /**
     * @return Template
     */
    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('panel', $this->getForm()->show());

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
<div class="">

  <div class="tk-panel" data-panel-icon="fa fa-users" var="panel"></div>
  
    <div class="tk-panel" data-panel-title="Example CSV Format" data-panel-icon="fa fa-info" var="info">
      <pre>mentorId, studentId
<b>115254,114241242</b>
username,114241242
email@example.com,114241242
</pre>
        <p>Both fields can be either staff/student numbers, emails, or username`s.</p>
        <p>Recommended: Use the Staff Number for the mentorId and the Student Number for the studentId</p>
    
    </div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}