<?php
namespace Uni\Form\Renderer;

use Tk\Form\Field;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class HorizontalFieldGroup extends \Tk\Form\Renderer\FieldGroup
{


    /**
     * @return \Dom\Renderer\Renderer|\Dom\Template|null
     * @throws \Exception
     */
    public function show()
    {
        $t = parent::show();
        return $t;
    }

    /**
     * makeTemplate
     *
     * @return string
     */
    protected function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="form-group has-feedback" var="field-group">
  <span class="col-md-offset-2 col-md-10 help-block error-block"><span class="fa fa-ban" choice="errorText"></span><span var="errorText" choice="errorText"></span></span>
  <label class="col-md-2 control-label" var="label" choice="label">&nbsp;</label>
  <div class="col-md-10">
    <div var="element" class="controls"></div>
    <span class="help-block help-text" var="notes" choice="notes"></span>
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }

}
