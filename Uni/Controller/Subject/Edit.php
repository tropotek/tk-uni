<?php
namespace Uni\Controller\Subject;

use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\AdminEditIface
{


    /**
     * @var \Uni\Db\SubjectIface|\Uni\Db\Subject
     */
    protected $subject = null;



    /**
     * Edit constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Subject Edit');
    }

    /**
     * @param \Tk\Request $request
     * @return \Uni\Db\Subject|\Uni\Db\SubjectIface|null
     * @throws \Exception
     */
    protected function findSubject(\Tk\Request $request)
    {
        if (!$this->subject) {
            $this->subject = $this->getConfig()->getSubject();
            if (!$this->subject) {
                $this->subject = $this->getConfig()->createSubject();
                $this->subject->institutionId = $this->getConfig()->getInstitutionId();
                $this->subject->email = $this->getConfig()->getInstitution()->getEmail();
                if ($request->get('subjectId')) {
                    $this->subject = $this->getConfig()->getSubjectMapper()->find($request->get('subjectId'));
                    if ($this->getConfig()->getInstitutionId() != $this->subject->institutionId) {
                        \Tk\Alert::addError('You do not have permission to edit this subject.');
                        \Uni\Uri::createHomeUrl('/index.html')->redirect();
                    }
                }
            }
        }
        return $this->subject;
    }


    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function doDefault(\Tk\Request $request)
    {
        $this->subject = $this->findSubject($request);

        $this->setForm(\Uni\Form\Subject::create()->setModel($this->subject));
        $this->initForm($request);
        $this->getForm()->execute();

        $this->initActionPanel();

    }

    /**
     * Use this to init the form before execute is called
     * @param \Tk\Request $request
     */
    public function initForm(\Tk\Request $request) { }

    /**
     * @param \Tk\Request $request
     *
     * @deprecated use initForm()
     */
    protected function postInitForm(\Tk\Request $request) {
        $this->initForm($request);
        \Tk\Log::warning('Using Deprecated Method: ' . \Tk\Debug\StackTrace::dumpLine());
    }

    /**
     *
     */
    public function initActionPanel()
    {
        if ($this->subject->getId() && ($this->getUser()->isStaff() || $this->getUser()->isClient())) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Plugins',
                \Uni\Uri::createHomeUrl('/subject/'.$this->subject->getId().'/plugins.html')->set('subjectId', $this->subject->getId()), 'fa fa-plug'));

            if(!$this->getConfig()->isSubjectUrl()) {
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Enrollments',
                    \Uni\Uri::createHomeUrl('/subjectEnrollment.html')->set('subjectId', $this->subject->getId()), 'fa fa-list'));
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Students',
                    \Uni\Uri::createHomeUrl('/studentUserManager.html')->set('subjectId', $this->subject->getId()), 'fa fa-group'));
            } else {
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Enrollments',
                    \Uni\Uri::createSubjectUrl('/subjectEnrollment.html'), 'fa fa-list'));
                $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Students',
                    \Uni\Uri::createSubjectUrl('/studentUserManager.html'), 'fa fa-group'));
            }
        }
    }

    /**
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());
        if ($this->subject->getId()) {
            $template->setAttr('panel', 'data-panel-title', "'" . $this->subject->getName() . "' [ID: "  . $this->subject->getId() . ']');
        }

        return $template;
    }

    /**
     * @return \App\Db\Subject|null|\Uni\Db\Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="tk-panel" data-panel-title="Subject Edit" data-panel-icon="fa fa-graduation-cap" var="panel"></div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}