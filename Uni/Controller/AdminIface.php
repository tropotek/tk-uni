<?php
namespace Uni\Controller;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AdminIface extends Iface implements \Dom\Renderer\DisplayInterface
{

    /**
     * @var \Tk\Ui\Admin\ActionPanel
     */
    protected $actionPanel = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->actionPanel = \Uni\Config::createActionPanel();
    }

    /**
     * @return \Tk\Ui\Admin\ActionPanel
     */
    public function getActionPanel()
    {
        return $this->actionPanel;
    }


}