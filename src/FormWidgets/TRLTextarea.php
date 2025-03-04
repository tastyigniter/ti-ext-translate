<?php

declare(strict_types=1);

namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\Classes\BaseFormWidget;
use Override;

class TRLTextarea extends BaseFormWidget
{
    use TRLBase;

    public const string FALLBACK_TYPE = 'textarea';

    protected string $defaultAlias = 'trltextarea';

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
            return $this->makePartial('trltextarea/trltextarea');
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
    }
}
