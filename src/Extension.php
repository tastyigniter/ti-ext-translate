<?php

namespace Igniter\Translate;

use Igniter\System\Classes\BaseExtension;
use Igniter\Translate\Classes\EventRegistry;
use Illuminate\Support\Facades\Event;

/**
 * Translate Extension Information File
 */
class Extension extends BaseExtension
{
    public function register()
    {
        $this->app->singleton(EventRegistry::class);

        Event::listen('admin.form.extendFieldsBefore', function($widget) {
            resolve(EventRegistry::class)->registerFormFieldReplacements($widget);
        }, -1);
    }

    public function boot()
    {
        resolve(EventRegistry::class)->bootTranslatableModels();
    }

    public function registerFormWidgets(): array
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
