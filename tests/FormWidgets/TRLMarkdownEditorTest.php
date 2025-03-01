<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\System\Facades\Assets;
use Igniter\Translate\FormWidgets\TRLMarkdownEditor;
use Mockery;

use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
    $this->trlMarkdownEditor = new TRLMarkdownEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);
});

it('initializes locale correctly in TRLMarkdownEditor', function(): void {
    createSupportedLanguages();

    $this->trlMarkdownEditor->initialize();

    expect($this->trlMarkdownEditor->isSupported)->toBeTrue();
});

it('renders TRLMarkdownEditor with parent content', function(): void {
    $this->trlMarkdownEditor = new TRLMarkdownEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    expect($this->trlMarkdownEditor->render())->toBeString();
});

it('prepares variables correctly in TRLMarkdownEditor', function(): void {
    $this->trlMarkdownEditor = new TRLMarkdownEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    $this->trlMarkdownEditor->prepareVars();

    expect($this->trlMarkdownEditor->vars)->not->toBeEmpty();
});

it('loads assets correctly in TRLMarkdownEditor when locale is supported', function(): void {
    createSupportedLanguages();

    Assets::partialMock()->shouldReceive('addJs')->with('js/trlmarkdowneditor.js', null)->once();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->with('$/igniter/translate/assets/js/translatable.js', 'translatable-js')
        ->once();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->with('$/igniter/translate/assets/css/translatable.css', 'translatable-css')
        ->once();

    $this->trlMarkdownEditor->loadAssets();
});

it('returns save value correctly when locale is supported in TRLMarkdownEditor', function(): void {
    $this->trlMarkdownEditor->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'localeSaveValue']],
    ]);

    $result = $this->trlMarkdownEditor->getSaveValue('value');

    expect($result)->toBe('localeSaveValue');
});

it('returns original save value when locale is not supported in TRLMarkdownEditor', function(): void {
    $this->trlMarkdownEditor->isSupported = false;

    $result = $this->trlMarkdownEditor->getSaveValue('value');

    expect($result)->toBe('value');
});
