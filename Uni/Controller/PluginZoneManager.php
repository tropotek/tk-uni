<?php
namespace Uni\Controller;

use Tk\Db\Exception;
use Tk\Request;
use Dom\Template;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PluginZoneManager extends AdminIface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var string
     */
    protected $zoneName = '';

    /**
     * @var int
     */
    protected $zoneId = 0;

    /**
     *
     */
    public function __construct()
    {
        $this->setPageTitle('Plugin Manager');
    }

    /**
     * @param Request $request
     * @param string $zoneName
     * @param string $zoneId
     * @throws \Exception
     */
    public function doDefault(Request $request, $zoneName = '', $zoneId = '')
    {
        if (!$this->zoneName)
            $this->zoneName = $zoneName;
        if (!$this->zoneId)
            $this->zoneId = $zoneId;

        if (!$this->zoneName || !$this->zoneId) {
            throw new \Tk\Exception('Invalid zone plugin information?');
        }
        $this->setPageTitle($this->makeTitleFromZone($this->zoneName) . ' Plugin Manager');

        $this->pluginFactory = \Uni\Config::getInstance()->getPluginFactory();
        // Plugin manager table
        $this->table = \Uni\Config::getInstance()->createTable('PluginList');
        $this->table->setRenderer(\Uni\Config::getInstance()->createTableRenderer($this->table));

        $this->table->appendCell(new IconCell('icon'))->setLabel('');
        $this->table->appendCell(new ActionsCell($this->zoneName, $this->zoneId));
        $this->table->appendCell(new \Tk\Table\Cell\Text('name'))->addCss('key')->setOrderProperty('');
        $this->table->appendCell(new \Tk\Table\Cell\Text('version'))->setOrderProperty('');
        $this->table->appendCell(new \Tk\Table\Cell\Date('time'))->setLabel('Created')->setOrderProperty('');
        
        $this->table->setList($this->getPluginList());

    }

    /**
     * @return array
     */
    private function getPluginList()
    {
        try {
            $pluginFactory = \Uni\Config::getInstance()->getPluginFactory();
        } catch (Exception $e) {
        } catch (\Tk\Plugin\Exception $e) {
        }
        $plugins = $pluginFactory->getZonePluginList($this->zoneName);
        $list = array();
        /** @var \Tk\Plugin\Iface $plugin */
        foreach ($plugins as $plugin) {
            $info = $plugin->getInfo();
            $info->name = str_replace('ttek-plg/', '', $info->name);
            $list[] = $info;
        }
        return $list;
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // render Table
        $template->appendTemplate('table', $this->table->getRenderer()->show());

        $template->setAttr('table', 'data-panel-title', $this->makeTitleFromZone($this->zoneName) . ' Plugins');
        $template->insertText('zone', $this->makeTitleFromZone($this->zoneName));

        return $template;
    }

    /**
     * @param $str
     * @return string
     */
    protected function makeTitleFromZone($str)
    {
        $str = preg_replace('/[A-Z]/', ' $0', $str);
        $str = preg_replace('/[^a-z0-9]/i', ' ', $str);
        return ucwords($str);
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

  <div class="tk-panel" data-panel-title="Plugins" data-panel-icon="fa fa-compress" var="table"></div>
  
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}

class IconCell extends \Tk\Table\Cell\Text
{

    /**
     * OwnerCell constructor.
     *
     * @param string $property
     * @param null $label
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
    }

    /**
     * @param \StdClass $info
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     */
    public function getCellHtml($info, $rowIdx = null)
    {
        $template = $this->__makeTemplate();

        try {
            $pluginName = \Uni\Config::getInstance()->getPluginFactory()->cleanPluginName($info->name);
        } catch (Exception $e) {
        } catch (\Tk\Plugin\Exception $e) {
        }
        if (is_file(\Uni\Config::getInstance()->getPluginPath() . '/' . $pluginName . '/icon.png')) {
            $template->setAttr('icon', 'src', \Uni\Config::getInstance()->getPluginUrl() . '/' . $pluginName . '/icon.png');
            $template->setVisible('icon');
        }

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div>
  <img class="media-object" src="#" var="icon" style="width: 20px;" choice="icon"/>
</div>
HTML;
        return \Dom\Loader::load($html);
    }
}

class ActionsCell extends \Tk\Table\Cell\Text
{

    /**
     * @var string
     */
    protected $zoneName = '';

    /**
     * @var int
     */
    protected $zoneId = 0;


    /**
     * ActionsCell constructor.
     * @param string $zoneName
     * @param int $zoneId
     */
    public function __construct($zoneName, $zoneId)
    {
        parent::__construct('actions');
        $this->setOrderProperty('');
        $this->zoneName = $zoneName;
        $this->zoneId = $zoneId;
    }

    /**
     * Called when the Table::execute is called
     */
    public function execute() {
        /** @var \Tk\Request $request */
        $request = \Uni\Config::getInstance()->getRequest();

        if ($request->has('enable')) {
            $this->doEnablePlugin($request);
        } else if ($request->has('disable')) {
            $this->doDisablePlugin($request);
        }

    }

    /**
     * @param \StdClass $info
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|\Dom\Template
     * @throws \Exception
     */
    public function getCellHtml($info, $rowIdx = null)
    {
        $template = $this->__makeTemplate();

        try {
            $pluginFactory = \Uni\Config::getInstance()->getPluginFactory();
        } catch (Exception $e) { }

        $pluginName = $pluginFactory->cleanPluginName($info->name);
        /** @var \Tk\Plugin\Iface $plugin */
        $plugin = $pluginFactory->getPlugin($pluginName);

        if ($plugin->isZonePluginEnabled($this->zoneName, $this->zoneId)) {
            $this->getRow()->addCss('plugin-active');
            $template->setVisible('disable');
            $template->setAttr('disable', 'href', \Tk\Uri::create()->set('disable', $plugin->getName()));
            $settingsUrl = $plugin->getZoneSettingsUrl($this->zoneName, $this->zoneId);
            if ($settingsUrl) {
                $template->setAttr('title', 'href', $settingsUrl);
                $template->setAttr('setup', 'href', $settingsUrl->set('zoneId', $this->zoneId));
                $template->setVisible('setup');
            }
        } else {
            $this->getRow()->addCss('plugin-inactive');
            $template->setVisible('enable');
            $template->setAttr('enable', 'href', \Tk\Uri::create()->set('enable', $plugin->getName()));
        }

        $js = <<<JS
jQuery(function ($) {
    $('a.act').click(function (e) {
        return confirm('Are you sure you want to enable this plugin?');
    });
    $('a.deact').click(function (e) {
        return confirm('Are you sure you want to disable this plugin?');
    });
});
JS;
        $template->appendJs($js);

        $css = <<<CSS
#PluginList .plugin-inactive td {
  opacity: 0.5;
}
#PluginList .plugin-inactive td.mActions {
  opacity: 1;  
}
CSS;
        $template->appendCss($css);

        return $template;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $html = <<<HTML
<div class="text-right">
  <a href="#" class="btn btn-success btn-xs noblock act" choice="enable" var="enable" title="Enable Plugin"><i class="fa fa-sign-in"></i></a>
  <a href="#" class="btn btn-primary btn-xs noblock setup" choice="setup" var="setup" title="Configure Plugin"><i class="fa fa-cog"></i></a>
  <a href="#" class="btn btn-danger btn-xs noblock deact" choice="disable" var="disable" title="Disable Plugin"><i class="fa fa-remove"></i></a>
</div>
HTML;
        return \Dom\Loader::load($html);
    }

    protected function doEnablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('enable')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            \Uni\Config::getInstance()->getPluginFactory()->enableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
            \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` enabled.');
        }catch (\Exception $e) {
            \Tk\Alert::addError('Plugin `' . $pluginName . '` cannot be enabled.');
        }
        \Tk\Uri::create()->remove('enable')->redirect();
    }

    protected function doDisablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('disable')));
        if (!$pluginName) {
            \Tk\Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            \Uni\Config::getInstance()->getPluginFactory()->disableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
        } catch (Exception $e) {
        } catch (\Tk\Plugin\Exception $e) {
        }
        \Tk\Alert::addSuccess('Plugin `' . $pluginName . '` disabled');
        \Tk\Uri::create()->remove('disable')->redirect();
    }

}




