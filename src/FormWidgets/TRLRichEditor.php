<?php

declare(strict_types=1);

namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Classes\FormField;
use Override;
use Igniter\Admin\FormWidgets\RichEditor;
use Igniter\System\Models\Language;

class TRLRichEditor extends RichEditor
{
    use TRLBase;

    /**
     * {@inheritDoc}
     */
    protected string $defaultAlias = 'trlricheditor';

    public $originalAssetPath;

    public $originalViewPath;

    public function __construct(AdminController $controller, FormField $formField, array $configuration = [])
    {
        $this->parentPartialPath[] = '~/app/admin/formwidgets';
        $this->parentPartialPath[] = '~/app/admin/formwidgets/richeditor';
        $this->parentAssetPath[] = '~/app/admin/formwidgets/richeditor/assets';

        parent::__construct($controller, $formField, $configuration);
    }

    #[Override]
    public function initialize(): void
    {
        parent::initialize();

        $this->initLocale();
    }

    #[Override]
    public function render(): string
    {
        $parentContent = $this->maskAsParent(fn(): string => parent::render());

        if (!$this->isSupported) {
            return $parentContent;
        }

        $this->vars['richeditor'] = $parentContent;

        return $this->makePartial('trlricheditor/trlricheditor');
    }

    #[Override]
    public function prepareVars(): void
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    #[Override]
    public function loadAssets(): void
    {
        $this->maskAsParent(function(): void {
            parent::loadAssets();
        });

        if (Language::supportsLocale()) {
            $this->loadLocaleAssets();
            $this->addJs('js/trlricheditor.js');
        }
    }

    /**
     * Returns an array of translated values for this field
     * @return array
     */
    #[Override]
    public function getSaveValue(mixed $value): mixed
    {
        if (!$this->isSupported) {
            return $value;
        }

        return $this->getLocaleSaveValue($value);
    }
}
