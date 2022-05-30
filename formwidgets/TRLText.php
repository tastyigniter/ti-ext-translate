<?php

namespace Igniter\Translate\FormWidgets;

use Admin\Classes\BaseFormWidget;

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
        $this->prepareLocaleVars();

        if ($this->isSupported)
            return $this->makePartial('trltext/trltext');

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
        $this->addJs('$/igniter/translate/formwidgets/trlrepeater/assets/js/trlrepeater.js', 'trlrepeater-js');
    }
}
