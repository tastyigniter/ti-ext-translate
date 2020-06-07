<?php namespace Igniter\Translate;

use Igniter\Translate\Classes\EventRegistry;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;

/**
 * Translate Extension Information File
 */
class Extension extends BaseExtension
{
    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {
        Event::listen('admin.form.extendFieldsBefore', function ($widget) {
            EventRegistry::instance()->registerFormFieldReplacements($widget);
        }, -1);
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
// Remove this line and uncomment the line below to activate
//            'Igniter\Translate\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
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
            'Igniter\Translate\FormWidgets\TRLMediaFinder' => ['code' => 'trlmediafinder'],
        ];
    }
}
