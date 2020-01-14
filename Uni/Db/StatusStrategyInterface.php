<?php

namespace Uni\Db;

use Dom\Template;
use Exception;
use Tk\Mail\CurlyMessage;
use Tk\ObjectUtil;

/**
 *
 * @author Tropotek <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Tropotek
 */
abstract class StatusStrategyInterface
{
    /**
     * @var Status
     */
    private $status = null;

    /**
     * StatusStrategyInterface constructor.
     * @param Status $status
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * return true to trigger the status change events
     *
     * @param Status $status
     * @return boolean
     */
    public abstract function triggerStatusChange($status);

    /**
     * Format a message with the information related to the status and the model using the CurlyMessage syntax.
     * 
     * This should be called before you send the status emails, here you can add the correct recipiants, and 
     *    add template parames to fill in your mail CurlyMessage boty template.  
     *
     * @param Status $status
     * @param CurlyMessage $message
     */
    public abstract function formatStatusMessage($status, $message);


    // pending notification system calls

    /**
     * @return string|Template
     */
    public function getPendingIcon()
    {
        return sprintf('<div class="status-icon bg-tertiary" title="Status Pending"><i class="fa fa-hourglass-half"></i></div>');
    }

    /**
     * @return string|Template
     * @throws Exception
     */
    public function getPendingHtml()
    {
        $user = $this->getStatus()->getUser();
        $u = '[Unknown]';
        if ($user) $u = $user->getName();
        return sprintf('<em>%s</em> triggered a pending status for %s [ID: %s]',
            $u, $this->getLabel(), $this->getStatus()->getFid()
        );
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return ucfirst(preg_replace('/[A-Z]/', ' $0', ObjectUtil::basename($this->getStatus()->getFkey())));
    }

}