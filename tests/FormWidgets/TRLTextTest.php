<?php

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\System\Facades\Assets;
use Igniter\Translate\FormWidgets\TRLText;
use Mockery;
use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function() {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
    $this->trlText = new TRLText(resolve(Menus::class), $this->formField, ['model' => $this->model]);
});

it('initializes locale correctly in TRLText', function() {
    createSupportedLanguages();

    $this->trlText->initialize();

    expect($this->trlText->isSupported)->toBeTrue();
});

it('renders TRLText with locale support', function() {
    expect($this->trlText->render())->toBeString();
});

it('renders parent content only when locale is not supported in TRLText', function() {
    $this->trlText->isSupported = false;

    expect($this->trlText->render())->toBeString();
});

it('returns save value correctly when locale is supported in TRLText', function() {
    createSupportedLanguages();

    $this->trlText->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'localeSaveValue']],
    ]);

    $result = $this->trlText->getSaveValue('value');

    expect($result)->toBe('localeSaveValue');
});

it('returns original save value when locale is not supported in TRLText', function() {
    $this->trlText->isSupported = false;

    $result = $this->trlText->getSaveValue('value');

    expect($result)->toBe('value');
});

it('loads assets correctly in TRLText', function() {
    createSupportedLanguages();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->with('$/igniter/translate/formwidgets/trlrepeater/assets/js/trlrepeater.js', 'trlrepeater-js')
        ->once();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->with('$/igniter/translate/assets/js/translatable.js', 'translatable-js')
        ->once();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->with('$/igniter/translate/assets/css/translatable.css', 'translatable-css')
        ->once();

    $this->trlText->loadAssets();
});

it('returns locale value correctly when model has custom mutate method in TRLBase', function() {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['en' => ['testField' => 'value']],
    ]);

    $this->model->shouldReceive('methodExists')->with('getTestFieldAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('getTestFieldAttributeTranslatedValue', ['en'])->andReturn('translatedValue');

    $result = $this->trlText->getLocaleValue('en');

    expect($result)->toBe('translatedValue');
});

it('returns locale value correctly when model has generic mutate method in TRLBase', function() {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['en' => ['testField' => 'value']],
    ]);

    $this->trlText->activeLocale->code = 'fr';
    $this->model->shouldReceive('methodExists')->with('getAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('translatableNoFallbackLocale', [])->andReturnSelf();
    $this->model->shouldReceive('extendableCall')
        ->with('getAttributeTranslatedValue', ['testField', 'en'])
        ->andReturn('translatedValue');

    $result = $this->trlText->getLocaleValue('en');

    expect($result)->toBe('translatedValue');
});

it('returns locale save value correctly when model has custom mutate method in TRLBase', function() {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'testValue']],
    ]);

    $this->model->shouldReceive('methodExists')->with('setTestFieldAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('setTestFieldAttributeTranslatedValue', ['testValue', 'en']);

    $result = $this->trlText->getLocaleSaveValue('defaultValue');

    expect($result)->toBe('testValue');
});

it('returns locale save value correctly when model has generic mutate method in TRLBase', function() {
    createSupportedLanguages();
    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'testValue']],
    ]);

    $this->model->shouldReceive('methodExists')->with('setAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('setAttributeTranslatedValue', ['testField', 'testValue', 'en']);

    $result = $this->trlText->getLocaleSaveValue('defaultValue');

    expect($result)->toBe('testValue');
});

it('returns empty array when locale save data is not an array in TRLBase', function() {
    mockRequest([
        'TRLTranslate' => 'not an array',
    ]);

    $result = $this->trlText->getLocaleSaveData('testField');

    expect($result)->toBe([]);
});

it('returns empty array when locale save data is empty in TRLBase', function() {
    mockRequest([
        'TRLTranslate' => [],
    ]);

    $result = $this->trlText->getLocaleSaveData('testField');

    expect($result)->toBe([]);
});
