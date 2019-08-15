<?php
namespace Uni\Ui\Dialog;


/**
 * This class uses the bootstrap dialog box model
 * @see http://getbootstrap.com/javascript/#modals
 *
 *
 * <code>
 * // doDefault()
 * $this->dialog = new \App\Ui\Dialog\FindUser('Enroll Student');
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
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class AjaxSelect extends Iface
{
    /**
     * @var null|callable
     */
    protected $onSelect = null;

    /**
     * @var \Tk\Uri
     */
    protected $ajaxUrl = null;

    /**
     * @var array
     */
    protected $ajaxParams = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $notes = '';


    /**
     * DialogBox constructor.
     *
     * @param $title
     * @param \Tk\Uri $ajaxUrl
     */
    public function __construct($title, $ajaxUrl = null)
    {
        throw new \Tk\Exception('deprecated');
        parent::__construct($title);
        $this->ajaxUrl = $ajaxUrl;
        $this->addButton('Close');
    }

    /**
     * @param $params
     * @return $this
     */
    public function setAjaxParams($params)
    {
        $this->ajaxParams = $params;
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     * @throws \Tk\Exception
     */
    public function setOnSelect($callable)
    {
        if (!is_callable($callable)) {
            throw new \Tk\Exception('Must pass a callable object.');
        }
        $this->onSelect = $callable;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelectButtonId()
    {
        return $this->getId().'-select';
    }

    /**
     * @param $notes
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Process the enrolments as submitted from the dialog
     *
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute(\Tk\Request $request)
    {
        $eventId = $this->getSelectButtonId();
        // Fire the callback if set
        if ($request->has($eventId)) {
            $this->data = $request->all();
            $redirect = \Tk\Uri::create();
            if (is_callable($this->onSelect)) {
                $url = call_user_func_array($this->onSelect, array($this->data));
                if ($url instanceof \Tk\Uri) {
                    $redirect = $url;
                }
            }
            $redirect->remove($this->getSelectButtonId())->remove('selectedId')->redirect();
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->getTemplate()->addCss('dialog', 'tk-dialog-ajax-select');
        $template = $this->makeBodyTemplate();
        if ($this->notes) {
            $template->insertHtml('notes', $this->notes);
            $template->setVisible('notes');
        }

        $ajaxUrl = $this->ajaxUrl->toString();
        $actionUrl = \Uni\Uri::create()->set($this->getSelectButtonId())->toString();
        $ajaxParams = json_encode($this->ajaxParams,\JSON_FORCE_OBJECT);

        $this->getTemplate()->setAttr('dialog', 'data-ajax-url', $ajaxUrl);
        $this->getTemplate()->setAttr('dialog', 'data-action-url', $actionUrl);
        $this->getTemplate()->setAttr('dialog', 'data-ajax-params', $ajaxParams);

        $js = <<<JS
jQuery(function($) {
  
  $('.tk-dialog-ajax-select').each(function () {
    let dialog = $(this);
    let settings = $.extend({}, {
        selectParam : 'id'
      }, dialog.data());
    
    let launchBtn = null;
    let launchData = {};
    processing(false);
    
    dialog.find('.btn-search').click(function(e) {
      processing(true);
      if (dialog.find('.input-search').val())
        settings.ajaxParams.keywords = dialog.find('.input-search').val();
      
      $.get(settings.ajaxUrl, settings.ajaxParams, function (data) {
        let panel = dialog.find('.dialog-table').empty();
        let table = buildTable(data);
        panel.append(table);
        processing(false);
      });
    });
  
    function buildTable(data) {
      if (data.length === 0) {
        return $('<p class="text-center" style="margin-top: 10px;font-weight: bold;font-style: italic;">No Data Found!</p>');
      }
      //let table = $('<table class="table" style="margin-top: 10px;"><tr><th>ID</th><th>Name</th></tr> <tr class="data-tpl"><td class="cell-id"></td><td class="cell-name"><a href="javascript:;" class="cell-name-url"></a></td></tr> </table>');
      let table = $('<table class="table" style="margin-top: 10px;"><tr><th>Name</th></tr> <tr class="data-tpl"><td class="cell-name"><a href="javascript:;" class="cell-name-url"></a></td></tr> </table>');
      $.each(data, function (i, obj) {
        let row = table.find('tr.data-tpl').clone();
        row.removeClass('data-tpl').addClass('data');
        var href = settings.actionUrl+'&selectedId=' + obj[settings.selectParam];
        if (!$.isEmptyObject(launchData)) {
          href += '&' + $.param(launchData);
        }
        row.find('.cell-name-url').text(obj.name).attr('href', href).on('click', function (e) {
          $(this).on('click', function() {return false;});
        });
        //row.find('.cell-id').text(obj.id);
        table.find('tr.data-tpl').after(row);
      });
      table.find('tr.data-tpl').remove();
      
      return table;
    }
    
    function processing(bool) {
      if (bool) {
        dialog.find('.form-control-feedback').show();
        dialog.find('.input-search').attr('disabled', 'disabled');
        dialog.find('.btn-search').attr('disabled', 'disabled');
        dialog.find('.cell-name-url').addClass('disabled');
      } else {
        dialog.find('.form-control-feedback').hide();
        dialog.find('.input-search').removeAttr('disabled');
        dialog.find('.btn-search').removeAttr('disabled');
        dialog.find('.cell-name-url').removeClass('disabled');
      }
    }
    
    // Some focus and key logic
    dialog.on('shown.bs.modal', function (e) {
      dialog.find('.input-search').val('').focus();
      launchBtn = $(e.relatedTarget);
      launchData = {};
      $.each(launchBtn.data(), function (k, v) {
        if (k === 'toggle' || k === 'target' || k === 'trigger') return;
        if (typeof v === 'string' || typeof v === 'number')
          launchData[k] = v;
      });
      dialog.find('.btn-search').click();
    });
    
    dialog.find('.input-search').on('keyup', function(e) {
      let code = (e.keyCode ? e.keyCode : e.which);
      if(code === 13) { //Enter keycode
          dialog.find('.btn-search').click();
      }    
    });
    
  });
  
  
  
});
JS;
        $template->appendJs($js);


        $this->setBody($template);
        return parent::show();
    }

    /**
     * @return \Dom\template
     */
    public function makeBodyTemplate()
    {
        $xhtml = <<<HTML
<div class="row">

  <div class="col-md-12">
    <p var="notes" choice="notes"></p>
    <div class="input-group has-feedback has-feedback-left">
      <input type="text" placeholder="Search by keyword ..." class="form-control input-sm input-search"/>
      <div class="form-control-feedback" style="">
        <i class="fa fa-spinner fa-spin"></i>
      </div>
      <span class="input-group-btn">
        <button type="button" class="btn btn-default btn-sm btn-search">Go!</button>
      </span>
    </div><!-- /input-group -->
  </div>
  
  <div class="col-md-12" >
    <div class="dialog-table" style="min-height: 100px;"></div>
  </div>
  
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
}
