<?php
namespace Uni\Controller;

use Dom\Loader;
use StdClass;
use Tk\Alert;
use Tk\Db\Exception;
use Tk\Log;
use Tk\Request;
use Dom\Template;
use Tk\Table;
use Tk\Table\Cell\ButtonCollection;
use Tk\Table\Cell\Date;
use Tk\Table\Cell\Text;
use Tk\Table\Ui\ActionButton;
use Uni\Uri;
use Uni\Config;

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
     * @var Table
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

        if ($request->has('enable')) {
            $this->doEnablePlugin($request);
        } else if ($request->has('disable')) {
            $this->doDisablePlugin($request);
        }


        $this->setPageTitle($this->makeTitleFromZone($this->zoneName) . ' Plugin Manager');

        $this->pluginFactory = Config::getInstance()->getPluginFactory();
        // Plugin manager table
        $this->table = Config::getInstance()->createTable('PluginList');
        $this->table->setRenderer(Config::getInstance()->createTableRenderer($this->table));

        //$this->table->appendCell(new IconCell('icon'))->setLabel('');
        $this->table->appendCell(new Text('icon'))->setOrderProperty('')->setLabel('#')
            ->addOnCellHtml(function (\Tk\Table\Cell\Iface $cell, $obj, $html) {
                /** @var $obj stdClass */
                $html = <<<HTML
        <div>
          <img class="media-object" src="#" var="icon" style="width: 20px;" choice="icon" />
        </div>
HTML;
                $template = Loader::load($html);
                try {
                    $pluginName = Config::getInstance()->getPluginFactory()->cleanPluginName($obj->name);
                } catch (\Exception $e) { Log::error($e->__toString());}

                if (is_file(Config::getInstance()->getPluginPath() . '/' . $pluginName . '/icon.png')) {
                    $template->setAttr('icon', 'src', Config::getInstance()->getPluginUrl() . '/' . $pluginName . '/icon.png');
                    $template->setVisible('icon');
                }
                return $template;
            });




        //$this->table->appendCell(new ActionsCell($this->zoneName, $this->zoneId));

        $zoneName = $this->zoneName;
        $zoneId = $this->zoneId;
        $actionsCell = ButtonCollection::create('actions');
        $actionsCell->append(ActionButton::createBtn('Enable Plugin ', '#', 'fa fa-sign-in'))
            ->addCss('btn-success btn-xs noblock act')
            ->addOnShow(function (ButtonCollection $cell, $obj, ActionButton $button) use ($zoneName, $zoneId) {
                /* @var $obj stdClass */
                $pluginFactory = Config::getInstance()->getPluginFactory();
                $pluginName = $pluginFactory->cleanPluginName($obj->name);
                /** @var \Tk\Plugin\Iface $plugin */
                $plugin = $pluginFactory->getPlugin($pluginName);
                if ($plugin->isZonePluginEnabled($zoneName, $zoneId)) {
                    $cell->getRow()->addCss('plugin-active');
                    $button->setVisible(false);
                } else {
                    $cell->getRow()->addCss('plugin-inactive');
                    $button->setUrl(Uri::create()->set('enable', $plugin->getName()));
                }
            });
        $actionsCell->append(ActionButton::createBtn('Configure Plugin ', '#', 'fa fa-cog'))
            ->addCss('btn-primary btn-xs noblock setup')
            ->addOnShow(function (ButtonCollection $cell, $obj, ActionButton $button) use ($zoneName, $zoneId) {
                /* @var $obj stdClass */
                $pluginFactory = Config::getInstance()->getPluginFactory();
                $pluginName = $pluginFactory->cleanPluginName($obj->name);
                /** @var \Tk\Plugin\Iface $plugin */
                $plugin = $pluginFactory->getPlugin($pluginName);
                if ($plugin->isZonePluginEnabled($zoneName, $zoneId)) {
                    $cell->getRow()->addCss('plugin-active');
                    $settingsUrl = $plugin->getZoneSettingsUrl($this->zoneName, $this->zoneId);
                    $button->setUrl($settingsUrl->set('zoneId', $this->zoneId));
                } else {
                    $cell->getRow()->addCss('plugin-inactive');
                    $button->setVisible(false);
                }
            });
        $actionsCell->append(ActionButton::createBtn('Disable Plugin ', '#', 'fa fa-remove'))
            ->addCss('btn-danger btn-xs noblock deact')
            ->addOnShow(function (ButtonCollection $cell, $obj, ActionButton $button) use ($zoneName, $zoneId) {
                /* @var $obj stdClass */
                $pluginFactory = Config::getInstance()->getPluginFactory();
                $pluginName = $pluginFactory->cleanPluginName($obj->name);
                /** @var \Tk\Plugin\Iface $plugin */
                $plugin = $pluginFactory->getPlugin($pluginName);
                if ($plugin->isZonePluginEnabled($zoneName, $zoneId)) {
                    $cell->getRow()->addCss('plugin-active');
                    $button->setUrl(Uri::create()->set('disable', $plugin->getName()));
                } else {
                    $cell->getRow()->addCss('plugin-inactive');
                    $button->setVisible(false);
                }
            });

        $this->table->appendCell($actionsCell)->addOnCellHtml(function (\Tk\Table\Cell\Iface $cell, $obj, $html) {
            /** @var $obj stdClass */
            $template = $cell->getTable()->getRenderer()->getTemplate();

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

            return $html;
        });



        $this->table->appendCell(new Text('name'))->addCss('key')->setOrderProperty('');
        $this->table->appendCell(new Text('version'))->setOrderProperty('');
        $this->table->appendCell(new Date('time'))->setLabel('Created')->setOrderProperty('');
        
        $this->table->setList($this->getPluginList());

    }

    /**
     * @return array
     */
    private function getPluginList()
    {
        try {
            $pluginFactory = Config::getInstance()->getPluginFactory();
        } catch (\Exception $e) { Log::error($e->__toString()); }
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

    protected function doEnablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('enable')));
        if (!$pluginName) {
            Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            Config::getInstance()->getPluginFactory()->enableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
            Alert::addSuccess('Plugin `' . $pluginName . '` enabled.');
        }catch (\Exception $e) {
            Alert::addError('Plugin `' . $pluginName . '` cannot be enabled.');
        }
        Uri::create()->remove('enable')->redirect();
    }

    protected function doDisablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('disable')));
        if (!$pluginName) {
            Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            Config::getInstance()->getPluginFactory()->disableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
        } catch (\Exception $e) {
            Alert::addError('Plugin `' . $pluginName . '` cannot be disabled.');
        }
        Alert::addSuccess('Plugin `' . $pluginName . '` disabled');
        Uri::create()->remove('disable')->redirect();
    }

    /**
     * @return Template
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
        return Loader::load($xhtml);
    }

}


class ActionsCell extends Text
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
        /** @var Request $request */
        $request = Config::getInstance()->getRequest();

        if ($request->has('enable')) {
            $this->doEnablePlugin($request);
        } else if ($request->has('disable')) {
            $this->doDisablePlugin($request);
        }

    }

    /**
     * @param StdClass $info
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string|Template
     * @throws \Exception
     */
    public function getCellHtml($info, $rowIdx = null)
    {
        $template = $this->__makeTemplate();

        try {
            $pluginFactory = Config::getInstance()->getPluginFactory();
        } catch (Exception $e) { }

        $pluginName = $pluginFactory->cleanPluginName($info->name);
        /** @var \Tk\Plugin\Iface $plugin */
        $plugin = $pluginFactory->getPlugin($pluginName);

        if ($plugin->isZonePluginEnabled($this->zoneName, $this->zoneId)) {
            $this->getRow()->addCss('plugin-active');
            $template->setVisible('disable');
            $template->setAttr('disable', 'href', Uri::create()->set('disable', $plugin->getName()));
            $settingsUrl = $plugin->getZoneSettingsUrl($this->zoneName, $this->zoneId);
            if ($settingsUrl) {
                $template->setAttr('title', 'href', $settingsUrl);
                $template->setAttr('setup', 'href', $settingsUrl->set('zoneId', $this->zoneId));
                $template->setVisible('setup');
            }
        } else {
            $this->getRow()->addCss('plugin-inactive');
            $template->setVisible('enable');
            $template->setAttr('enable', 'href', Uri::create()->set('enable', $plugin->getName()));
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
     * @return Template
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
        return Loader::load($html);
    }

    protected function doEnablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('enable')));
        if (!$pluginName) {
            Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            Config::getInstance()->getPluginFactory()->enableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
            Alert::addSuccess('Plugin `' . $pluginName . '` enabled.');
        }catch (\Exception $e) {
            Alert::addError('Plugin `' . $pluginName . '` cannot be enabled.');
        }
        Uri::create()->remove('enable')->redirect();
    }

    protected function doDisablePlugin(Request $request)
    {
        $pluginName = strip_tags(trim($request->get('disable')));
        if (!$pluginName) {
            Alert::addWarning('Cannot locate Plugin: ' . $pluginName);
            return;
        }
        try {
            Config::getInstance()->getPluginFactory()->disableZonePlugin($pluginName, $this->zoneName, $this->zoneId);
        } catch (\Exception $e) {
            Alert::addError('Plugin `' . $pluginName . '` cannot be disabled.');
        }
        Alert::addSuccess('Plugin `' . $pluginName . '` disabled');
        Uri::create()->remove('disable')->redirect();
    }

}




