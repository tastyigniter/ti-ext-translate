<?php

namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\FormWidgets\MarkdownEditor;
use Igniter\System\Models\Language;

class TRLMarkdownEditor extends MarkdownEditor
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    /**
     * {@inheritDoc}
     */
    protected string $defaultAlias = 'trlmarkdowneditor';

    public $originalAssetPath;

    public $originalViewPath;

    public function __construct($controller, $formField, $configuration = [])
    {
        $this->parentPartialPath[] = 'igniter.admin::_partials.formwidgets';
        $this->parentPartialPath[] = 'igniter.admin::_partials.formwidgets.markdowneditor';
        $this->parentAssetPath[] = 'igniter.admin::_partials.formwidgets.markdowneditor';

        parent::__construct($controller, $formField, $configuration);
    }

    public function initialize()
    {
        parent::initialize();

        $this->initLocale();
    }

    public function render()
    {
        $parentContent = $this->maskAsParent(function () {
            return parent::render();
        });

        $this->vars['markdowneditor'] = $parentContent;

        return $this->makePartial('trlmarkdowneditor/trlmarkdowneditor');
    }

    public function prepareVars()
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    public function loadAssets()
    {
        $this->maskAsParent(function () {
            parent::loadAssets();
        });

        if (Language::supportsLocale()) {
            $this->loadLocaleAssets();
            $this->addJs('js/trlmarkdowneditor.js');
        }
    }

    /**
     * Returns an array of translated values for this field
     * @return array
     */
    public function getSaveValue(mixed $value): mixed
    {
        if (!$this->isSupported) {
            return $value;
        }

        return $this->getLocaleSaveValue($value);
    }
}
