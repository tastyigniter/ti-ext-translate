<?php

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\System\Facades\Assets;
use Igniter\Translate\FormWidgets\TRLTextarea;
use Mockery;
use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function() {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
    $this->trlTextarea = new TRLTextarea(resolve(Menus::class), $this->formField, ['model' => $this->model]);
});

it('initializes locale correctly in TRLTextarea', function() {
    createSupportedLanguages();

    $this->trlTextarea->initialize();

    expect($this->trlTextarea->isSupported)->toBeTrue();
});

it('renders TRLTextarea with locale support', function() {
    expect($this->trlTextarea->render())->toBeString();
});

it('renders parent content only when locale is not supported in TRLTextarea', function() {
    $this->trlTextarea->isSupported = false;

    expect($this->trlTextarea->render())->toBeString();
});

it('returns save value correctly when locale is supported in TRLTextarea', function() {
    createSupportedLanguages();

    $this->trlTextarea->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'localeSaveValue']],
    ]);

    $result = $this->trlTextarea->getSaveValue('value');

    expect($result)->toBe('localeSaveValue');
});

it('returns original save value when locale is not supported in TRLTextarea', function() {
    $this->trlTextarea->isSupported = false;

    $result = $this->trlTextarea->getSaveValue('value');

    expect($result)->toBe('value');
});

it('loads assets correctly in TRLTextarea', function() {
    createSupportedLanguages();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->with('$/igniter/translate/assets/js/translatable.js', 'translatable-js')
        ->once();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->with('$/igniter/translate/assets/css/translatable.css', 'translatable-css')
        ->once();

    $this->trlTextarea->loadAssets();
});
