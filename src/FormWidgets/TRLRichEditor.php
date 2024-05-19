<?php

namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\FormWidgets\RichEditor;
use Igniter\System\Models\Language;

class TRLRichEditor extends RichEditor
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    /**
     * {@inheritDoc}
     */
    protected string $defaultAlias = 'trlricheditor';

    public $originalAssetPath;

    public $originalViewPath;

    public function __construct($controller, $formField, $configuration = [])
    {
        $this->parentPartialPath[] = '~/app/admin/formwidgets';
        $this->parentPartialPath[] = '~/app/admin/formwidgets/richeditor';
        $this->parentAssetPath[] = '~/app/admin/formwidgets/richeditor/assets';

        parent::__construct($controller, $formField, $configuration);
    }

    public function initialize()
    {
        parent::initialize();

        $this->initLocale();
    }

    public function render()
    {
        $parentContent = $this->maskAsParent(function() {
            return parent::render();
        });

        if (!$this->isSupported) {
            return $parentContent;
        }

        $this->vars['richeditor'] = $parentContent;

        return $this->makePartial('trlricheditor/trlricheditor');
    }

    public function prepareVars()
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    public function loadAssets()
    {
        $this->maskAsParent(function() {
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
    public function getSaveValue(mixed $value): mixed
    {
        if (!$this->isSupported) {
            return $value;
        }

        return $this->getLocaleSaveValue($value);
    }
}
