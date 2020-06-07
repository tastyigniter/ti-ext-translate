<?php namespace Igniter\Translate\FormWidgets;

use Admin\Classes\BaseFormWidget;
use System\Models\Languages_model;

class TRLText extends BaseFormWidget
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    protected $defaultAlias = 'trltext';

    public function initialize()
    {
        $this->initLocale();
    }

    public function render()
    {
        $this->isSupported = Languages_model::supportsLocale();

        if ($this->isSupported) {
            $this->prepareLocaleVars();

            return $this->makePartial('trltext/trltext');
        }

        return $this->renderFallbackField();
    }

    public function getSaveValue($value)
    {
        return $this->getLocaleSaveValue($value);
    }

    public function loadAssets()
    {
        $this->loadLocaleAssets();
    }
}
