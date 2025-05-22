<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\System\Facades\Assets;
use Igniter\Translate\FormWidgets\TRLTextarea;
use Mockery;

use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
    $this->trlTextarea = new TRLTextarea(resolve(Menus::class), $this->formField, ['model' => $this->model]);
});

it('initializes locale correctly in TRLTextarea', function(): void {
    createSupportedLanguages();

    $this->trlTextarea->initialize();

    expect($this->trlTextarea->isSupported)->toBeTrue();
});

it('renders TRLTextarea with locale support', function(): void {
    expect($this->trlTextarea->render())->toBeString();
});

it('renders parent content only when locale is not supported in TRLTextarea', function(): void {
    $this->trlTextarea->isSupported = false;

    expect($this->trlTextarea->render())->toBeString();
});

it('returns save value correctly when locale is supported in TRLTextarea', function(): void {
    createSupportedLanguages();

    $this->trlTextarea->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['en' => 'localeSaveValue']],
    ]);

    $result = $this->trlTextarea->getSaveValue('value');

    expect($result)->toBe('localeSaveValue');
});

it('returns original save value when locale is not supported in TRLTextarea', function(): void {
    $this->trlTextarea->isSupported = false;

    $result = $this->trlTextarea->getSaveValue('value');

    expect($result)->toBe('value');
});

it('loads assets correctly in TRLTextarea', function(): void {
    createSupportedLanguages();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->withArgs(fn($path, $name) => ends_with($path, '/js/translatable.js'))
        ->once();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->withArgs(fn($path, $name) => ends_with($path, '/css/translatable.css'))
        ->once();

    $this->trlTextarea->loadAssets();
});
