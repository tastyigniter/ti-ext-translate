<?php namespace Igniter\Translate;

use Igniter\Translate\Classes\EventRegistry;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;

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

    public function registerComponents()
    {
        return [
// Remove this line and uncomment the line below to activate
//            'Igniter\Translate\Components\MyComponent' => 'myComponent',
        ];
    }

    public function registerPermissions()
    {
// Remove this line and uncomment block to activate
        return [
//            'Igniter.Translate.SomePermission' => [
//                'description' => 'Some permission',
//                'group' => 'module',
//            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Igniter\Translate\FormWidgets\TRLText' => ['code' => 'trltext'],
            'Igniter\Translate\FormWidgets\TRLTextarea' => ['code' => 'trltextarea'],
            'Igniter\Translate\FormWidgets\TRLRichEditor' => ['code' => 'trlricheditor'],
            'Igniter\Translate\FormWidgets\TRLMarkdownEditor' => ['code' => 'trlmarkdowneditor'],
            'Igniter\Translate\FormWidgets\TRLRepeater' => ['code' => 'trlrepeater'],
        ];
    }
}
