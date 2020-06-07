<?php namespace Igniter\Translate\FormWidgets;

use Admin\Classes\BaseFormWidget;
use System\Models\Languages_model;

class TRLTextarea extends BaseFormWidget
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    protected $defaultAlias = 'trltextarea';

    const FALLBACK_TYPE = 'textarea';

    public function initialize()
    {
        $this->initLocale();
    }

    public function render()
    {
        $this->isSupported = Languages_model::supportsLocale();

        if ($this->isSupported) {
            $this->prepareLocaleVars();

            return $this->makePartial('trltextarea/trltextarea');
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
