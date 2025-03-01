<?php

declare(strict_types=1);

namespace Igniter\Translate;

use Override;
use Igniter\Translate\FormWidgets\TRLText;
use Igniter\Translate\FormWidgets\TRLTextarea;
use Igniter\Translate\FormWidgets\TRLRichEditor;
use Igniter\Translate\FormWidgets\TRLMarkdownEditor;
use Igniter\Translate\FormWidgets\TRLRepeater;
use Igniter\Admin\Widgets\Form;
use Igniter\System\Classes\BaseExtension;
use Igniter\Translate\Classes\EventRegistry;
use Illuminate\Support\Facades\Event;

/**
 * Translate Extension Information File
 */
class Extension extends BaseExtension
{
    public $singletons = [
        EventRegistry::class,
    ];

    #[Override]
    public function register(): void
    {
        parent::register();

        Event::listen('admin.form.extendFieldsBefore', function(Form $widget): void {
            resolve(EventRegistry::class)->registerFormFieldReplacements($widget);
        });
    }

    #[Override]
    public function boot(): void
    {
        resolve(EventRegistry::class)->bootTranslatableModels();
    }

    #[Override]
    public function registerFormWidgets(): array
    {
        return [
            TRLText::class => ['code' => 'trltext'],
            TRLTextarea::class => ['code' => 'trltextarea'],
            TRLRichEditor::class => ['code' => 'trlricheditor'],
            TRLMarkdownEditor::class => ['code' => 'trlmarkdowneditor'],
            TRLRepeater::class => ['code' => 'trlrepeater'],
        ];
    }
}
