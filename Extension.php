<?php

namespace Igniter\Translate;

use Igniter\Translate\Classes\EventRegistry;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;
use System\Facades\Assets;

/**
 * Translate Extension Information File
 */
class Extension extends BaseExtension
{
    public function register()
    {
        Event::listen('admin.form.extendFieldsBefore', function ($widget) {
            EventRegistry::instance()->registerFormFieldReplacements($widget);
        }, -1);
    }

    public function boot()
    {
        EventRegistry::instance()->bootTranslatableModels();

    }

    public function loadTranslatableAssets()
    {
//        Always load assets in admin area as a workaround for popups that are loaded via ajax (no assets loaded)
        if (app()->runningInAdmin()) {
            Assets::addJs('$/igniter/translate/assets/js/translatable.js', 'translatable-js');
            Assets::addCss('$/igniter/translate/assets/css/translatable.css', 'translatable-css');
        }
    }

    public function registerComponents()
    {
        return [
            \Igniter\Translate\Components\LocalePicker::class => [
                'code' => 'localePicker',
                'name' => 'Language Switcher',
                'description' => 'Displays a dropdown to select a front-end language.',
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            \Igniter\Translate\FormWidgets\TRLText::class => ['code' => 'trltext'],
            \Igniter\Translate\FormWidgets\TRLTextarea::class => ['code' => 'trltextarea'],
            \Igniter\Translate\FormWidgets\TRLRichEditor::class => ['code' => 'trlricheditor'],
            \Igniter\Translate\FormWidgets\TRLMarkdownEditor::class => ['code' => 'trlmarkdowneditor'],
            \Igniter\Translate\FormWidgets\TRLRepeater::class => ['code' => 'trlrepeater'],
        ];
    }
}
