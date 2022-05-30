<?php

namespace Igniter\Translate\FormWidgets;

use Admin\Classes\BaseFormWidget;

class TRLTextarea extends BaseFormWidget
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    const FALLBACK_TYPE = 'textarea';

    protected $defaultAlias = 'trltextarea';

    public function initialize()
    {
        $this->initLocale();
    }

    public function render()
    {
        $this->prepareLocaleVars();

        if ($this->isSupported)
            return $this->makePartial('trltextarea/trltextarea');

        return $this->renderFallbackField();
    }

    public function getSaveValue($value)
    {
        if (!$this->isSupported)
            return $value;

        return $this->getLocaleSaveValue($value);
    }

    public function loadAssets()
    {
        $this->loadLocaleAssets();
    }
}
