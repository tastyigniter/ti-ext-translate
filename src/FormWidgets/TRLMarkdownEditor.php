<?php namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\FormWidgets\MarkdownEditor;
use Igniter\System\Models\Language;

class TRLMarkdownEditor extends MarkdownEditor
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'trlmarkdowneditor';

    public $originalAssetPath;

    public $originalViewPath;

    public function __construct($controller, $formField, $configuration = [])
    {
        $this->parentPartialPath[] = '~/app/admin/formwidgets';
        $this->parentPartialPath[] = '~/app/admin/formwidgets/markdowneditor';
        $this->parentAssetPath[] = '~/app/admin/formwidgets/markdowneditor/assets';

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
    public function getSaveValue($value)
    {
        if (!$this->isSupported)
            return $value;

        return $this->getLocaleSaveValue($value);
    }
}
