<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests;

use Igniter\Admin\Widgets\Form;
use Igniter\Translate\Classes\EventRegistry;
use Igniter\Translate\Extension;
use Igniter\Translate\FormWidgets\TRLMarkdownEditor;
use Igniter\Translate\FormWidgets\TRLRepeater;
use Igniter\Translate\FormWidgets\TRLRichEditor;
use Igniter\Translate\FormWidgets\TRLText;
use Igniter\Translate\FormWidgets\TRLTextarea;
use Illuminate\Support\Facades\Event;
use Mockery;

beforeEach(function(): void {
    $this->extension = new Extension(app());
});

it('registers singletons', function(): void {
    expect($this->extension->singletons)->toContain(EventRegistry::class);
});

it('listens to admin.form.extendFieldsBefore event in register method', function(): void {
    Event::shouldReceive('listen')->with('admin.form.extendFieldsBefore', Mockery::type('Closure'))->once();

    $this->extension->register();
});

it('registers form field replacements in register method', function(): void {
    $eventRegistry = Mockery::mock(EventRegistry::class);
    $eventRegistry->shouldReceive('registerFormFieldReplacements')->once();
    app()->instance(EventRegistry::class, $eventRegistry);

    Event::shouldReceive('listen')->with('admin.form.extendFieldsBefore', Mockery::on(function($callback): true {
        $formWidget = new class extends Form
        {
            public function __construct() {}
        };
        $callback($formWidget);
        return true;
    }))->once();

    $this->extension->register();
});

it('boots translatable models in boot method', function(): void {
    $eventRegistry = Mockery::mock(EventRegistry::class);
    $eventRegistry->shouldReceive('bootTranslatableModels')->once();
    app()->instance(EventRegistry::class, $eventRegistry);

    $this->extension->boot();
});

it('registers form widgets correctly in registerFormWidgets method', function(): void {
    $result = $this->extension->registerFormWidgets();

    expect($result)->toBe([
        TRLText::class => ['code' => 'trltext'],
        TRLTextarea::class => ['code' => 'trltextarea'],
        TRLRichEditor::class => ['code' => 'trlricheditor'],
        TRLMarkdownEditor::class => ['code' => 'trlmarkdowneditor'],
        TRLRepeater::class => ['code' => 'trlrepeater'],
    ]);
});
