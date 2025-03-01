<?php

declare(strict_types=1);

namespace Igniter\Translate\FormWidgets;

use Override;
use Igniter\Admin\Classes\BaseFormWidget;

class TRLText extends BaseFormWidget
{
    use TRLBase;

    protected string $defaultAlias = 'trltext';

    #[Override]
    public function initialize(): void
    {
        $this->initLocale();
    }

    #[Override]
    public function render()
    {
        $this->prepareLocaleVars();

        if ($this->isSupported) {
            return $this->makePartial('trltext/trltext');
        }

        return $this->renderFallbackField();
    }

    #[Override]
    public function getSaveValue(mixed $value): mixed
    {
        if (!$this->isSupported) {
            return $value;
        }

        return $this->getLocaleSaveValue($value);
    }

    #[Override]
    public function loadAssets(): void
    {
        $this->loadLocaleAssets();
        $this->addJs('$/igniter/translate/formwidgets/trlrepeater/assets/js/trlrepeater.js', 'trlrepeater-js');
    }
}
