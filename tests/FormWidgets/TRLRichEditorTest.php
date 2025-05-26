<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\System\Facades\Assets;
use Igniter\System\Models\Language;
use Igniter\Translate\FormWidgets\TRLRichEditor;
use Mockery;

use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
});

it('initializes locale correctly in TRLRichEditor', function(): void {
    createSupportedLanguages();

    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    $trlRichEditor->initialize();

    expect($trlRichEditor->isSupported)->toBeTrue();
});

it('renders TRLRichEditor with parent content', function(): void {
    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    expect($trlRichEditor->render())->toBeString();
});

it('renders parent content only when locale is not supported in TRLRichEditor', function(): void {
    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);
    $trlRichEditor->isSupported = false;

    expect($trlRichEditor->render())->toBeString();
});

it('prepares variables correctly in TRLRichEditor', function(): void {
    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    $trlRichEditor->prepareVars();

    expect($trlRichEditor->vars)->not->toBeEmpty();
});

it('loads assets correctly in TRLRichEditor when locale is supported', function(): void {
    createSupportedLanguages();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->withArgs(fn($path, $name) => ends_with($path, '/js/trlricheditor.js'))
        ->twice();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->withArgs(fn($path, $name) => ends_with($path, '/js/translatable.js'))
        ->twice();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->withArgs(fn($path, $name) => ends_with($path, '/css/translatable.css'))
        ->twice();

    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    $trlRichEditor->loadAssets();
});

it('does not load locale assets in TRLRichEditor when locale is not supported', function(): void {
    Language::$localesCache = [];
    Language::$activeLanguage = null;
    Language::$supportedLocalesCache = null;

    Assets::partialMock()
        ->shouldNotReceive('addJs')
        ->withArgs(fn($path, $name) => ends_with($path, '/js/trlricheditor.js'));

    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);

    $trlRichEditor->loadAssets();
});

it('returns save value correctly when locale is supported in TRLRichEditor', function(): void {
    createSupportedLanguages();

    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);
    $trlRichEditor->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'localeSaveValue']],
    ]);

    $result = $trlRichEditor->getSaveValue('value');

    expect($result)->toBe('localeSaveValue');
});

it('returns original save value when locale is not supported in TRLRichEditor', function(): void {
    $trlRichEditor = new TRLRichEditor(resolve(Menus::class), $this->formField, ['model' => $this->model]);
    $trlRichEditor->isSupported = false;

    $result = $trlRichEditor->getSaveValue('value');

    expect($result)->toBe('value');
});
