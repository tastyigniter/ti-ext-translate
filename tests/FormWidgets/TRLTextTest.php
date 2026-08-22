<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Translation\Localization;
use Igniter\System\Facades\Assets;
use Igniter\Translate\FormWidgets\TRLText;
use Mockery;

use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
    $this->trlText = new TRLText(resolve(Menus::class), $this->formField, ['model' => $this->model]);
});

it('initializes locale correctly in TRLText', function(): void {
    createSupportedLanguages();

    $this->trlText->initialize();

    expect($this->trlText->isSupported)->toBeTrue();
});

it('falls back to default locale when active locale is empty in TRLBase', function(): void {
    $localization = Mockery::mock(Localization::class);
    $localization->shouldReceive('getLocale')->andReturn(null);
    $localization->shouldReceive('getDefaultLocale')->andReturn('en');
    app()->instance('translator.localization', $localization);

    $this->trlText->initialize();

    expect($this->trlText->activeLocale)->toBe('en');
});

it('falls back to app locale when localization locales are empty in TRLBase', function(): void {
    $localization = Mockery::mock(Localization::class);
    $localization->shouldReceive('getLocale')->andReturn(null);
    $localization->shouldReceive('getDefaultLocale')->andReturn(null);
    app()->instance('translator.localization', $localization);

    app()->setLocale('es');

    $this->trlText->initialize();

    expect($this->trlText->activeLocale)->toBe('es');
});

it('renders TRLText with locale support', function(): void {
    expect($this->trlText->render())->toBeString();
});

it('renders parent content only when locale is not supported in TRLText', function(): void {
    $this->trlText->isSupported = false;

    expect($this->trlText->render())->toBeString();
});

it('returns save value correctly when locale is supported in TRLText', function(): void {
    createSupportedLanguages();

    $this->trlText->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'localeSaveValue']],
    ]);

    $result = $this->trlText->getSaveValue('value');

    expect($result)->toBe('localeSaveValue');
});

it('returns original save value when locale is not supported in TRLText', function(): void {
    $this->trlText->isSupported = false;

    $result = $this->trlText->getSaveValue('value');

    expect($result)->toBe('value');
});

it('loads assets correctly in TRLText', function(): void {
    createSupportedLanguages();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->withArgs(
            fn($path, $name) => ends_with($path, '/js/trlrepeater.js'),
        )->once();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->withArgs(
            fn($path, $name) => ends_with($path, '/js/translatable.js'),
        )->once();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->withArgs(
            fn($path, $name) => ends_with($path, '/css/translatable.css'),
        )->once();

    $this->trlText->loadAssets();
});

it('returns locale value correctly when model has custom mutate method in TRLBase', function(): void {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['en' => ['testField' => 'value']],
    ]);

    $this->model->shouldReceive('methodExists')->with('getTestFieldAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('getTestFieldAttributeTranslatedValue', ['en'])->andReturn('translatedValue');

    $result = $this->trlText->getLocaleValue('en');

    expect($result)->toBe('translatedValue');
});

it('returns locale value correctly when model has generic mutate method in TRLBase', function(): void {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['en' => ['testField' => 'value']],
    ]);

    $this->trlText->activeLocale = 'fr';
    $this->model->shouldReceive('methodExists')->with('getAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('translatableNoFallbackLocale', [])->andReturnSelf();
    $this->model->shouldReceive('extendableCall')
        ->with('getAttributeTranslatedValue', ['testField', 'en'])
        ->andReturn('translatedValue');

    $result = $this->trlText->getLocaleValue('en');

    expect($result)->toBe('translatedValue');
});

it('returns locale save value correctly when model has custom mutate method in TRLBase', function(): void {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'testValue']],
    ]);

    $this->model->shouldReceive('methodExists')->with('setTestFieldAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('setTestFieldAttributeTranslatedValue', ['testValue', 'en']);

    $result = $this->trlText->getLocaleSaveValue('defaultValue');

    expect($result)->toBe('testValue');
});

it('returns locale save value correctly when model has generic mutate method in TRLBase', function(): void {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'testValue']],
    ]);

    $this->model->shouldReceive('methodExists')->with('setAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('setAttributeTranslatedValue', ['testField', 'testValue', 'en']);

    $result = $this->trlText->getLocaleSaveValue('defaultValue');

    expect($result)->toBe('testValue');
});

it('returns empty array when locale save data is not an array in TRLBase', function(): void {
    mockRequest([
        'TRLTranslate' => 'not an array',
    ]);

    $result = $this->trlText->getLocaleSaveData('testField');

    expect($result)->toBe([]);
});

it('returns empty array when locale save data is empty in TRLBase', function(): void {
    mockRequest([
        'TRLTranslate' => [],
    ]);

    $result = $this->trlText->getLocaleSaveData('testField');

    expect($result)->toBe([]);
});
