<?php
namespace Uni;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Uri extends \Bs\Uri
{
    
    /**
     * Create a URL in the form of '/{subjectCode}/index.html'
     *
     * @param null|string|\Tk\Uri $spec
     * @param null|Db\SubjectIface $subject
     * @param null|\Uni\Db\UserIface $user
     * @return string|\Tk\Uri|static
     * @throws \Tk\Exception
     */
    public static function createSubjectUrl($spec = null, $subject = null, $user = null)
    {
        if ($spec instanceof \Tk\Uri)
            return clone $spec;

        if ($subject === null)
            $subject = Config::getInstance()->getSubject();
        $subjectCode = '';
        if ($subject) {
            $subjectCode = $subject->code . '/';
        }
        return self::createHomeUrl($subjectCode . trim($spec,'/'), $user);
    }

    /**
     * Create a URL in the form of '/inst/{institution.hash}/index.html'
     *
     * @param null|string|\Tk\Uri $spec
     * @param null|Db\InstitutionIface $institution
     * @param bool $useDomain If set to false then the institution path is prepended (todo: see if this is needed)
     * @return string|\Tk\Uri|static
     */
    public static function createInstitutionUrl($spec = null, $institution = null, $useDomain = true)
    {
        if ($spec instanceof \Tk\Uri) {
            return clone $spec;
        }
        if ($institution === null) {
            $institution = Config::getInstance()->getInstitution();
        }
        if (!$institution) {
            return static::create($spec);
        }

        $url = self::create('/inst/' . $institution->getHash() . '/' . trim($spec,'/'));
        if ($institution->getDomain() && $useDomain) {
            // $url = self::create($spec);
            $url = $url->setHost($institution->getDomain());
        }

        return $url;
    }


}