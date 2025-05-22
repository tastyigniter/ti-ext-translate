<?php

declare(strict_types=1);

namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Classes\FormField;
use Igniter\Admin\FormWidgets\MarkdownEditor;
use Igniter\System\Models\Language;
use Override;

class TRLMarkdownEditor extends MarkdownEditor
{
    use TRLBase;

    /**
     * {@inheritDoc}
     */
    protected string $defaultAlias = 'trlmarkdowneditor';

    public $originalAssetPath;

    public $originalViewPath;

    public function __construct(AdminController $controller, FormField $formField, array $configuration = [])
    {
        $this->parentPartialPath[] = 'igniter.admin::_partials.formwidgets';
        $this->parentPartialPath[] = 'igniter.admin::_partials.formwidgets.markdowneditor';
        $this->parentAssetPath[] = 'igniter.admin::_partials.formwidgets.markdowneditor';

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

        $this->vars['markdowneditor'] = $parentContent;

        return $this->makePartial('trlmarkdowneditor/trlmarkdowneditor');
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
            $this->addJs('igniter.translate::/js/trlmarkdowneditor.js');
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
