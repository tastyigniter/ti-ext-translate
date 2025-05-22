<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Flame\Database\Model;
use Igniter\System\Facades\Assets;
use Igniter\Translate\FormWidgets\TRLRepeater;
use Mockery;
use ReflectionClass;

use function Igniter\Translate\Tests\createSupportedLanguages;
use function Igniter\Translate\Tests\mockRequest;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->formField = new FormField('testField', 'Label');
    $this->trlRepeater = new TRLRepeater(resolve(Menus::class), $this->formField, [
        'model' => $this->model,
        'form' => ['fields' => []],
    ]);
});

it('initializes locale correctly in TRLRepeater', function(): void {
    createSupportedLanguages();

    $this->trlRepeater->initialize();

    expect($this->trlRepeater->isSupported)->toBeTrue();
});

it('renders TRLRepeater with parent content', function(): void {
    expect($this->trlRepeater->render())->toBeString();
});

it('renders parent content only when locale is not supported in TRLRepeater', function(): void {
    $this->trlRepeater->isSupported = false;

    expect($this->trlRepeater->render())->toBeString();
});

it('prepares variables correctly in TRLRepeater', function(): void {
    $this->trlRepeater->prepareVars();

    expect($this->trlRepeater->vars)->not->toBeEmpty();
});

it('loads assets correctly in TRLRepeater when locale is supported', function(): void {
    createSupportedLanguages();

    Assets::partialMock()->shouldReceive('addJs')->withArgs(
        fn($path, $name) => ends_with($path, '/js/trlrepeater.js'),
    )->once();

    Assets::partialMock()
        ->shouldReceive('addJs')
        ->withArgs(fn($path, $name) => ends_with($path, '/js/translatable.js'))
        ->once();

    Assets::partialMock()
        ->shouldReceive('addCss')
        ->withArgs(fn($path, $name) => ends_with($path, '/css/translatable.css'))
        ->once();

    $this->trlRepeater->loadAssets();
});

it('processes item definitions correctly in TRLRepeater', function(): void {
    $trlRepeater = new TRLRepeater(resolve(Menus::class), $this->formField, [
        'model' => $this->model,
        'form' => [
            'fields' => [
                ['type' => 'text', 'attributes' => []],
                ['type' => 'textarea', 'attributes' => []],
                ['type' => 'dropdown', 'attributes' => []],
            ],
        ],
    ]);

    $reflection = new ReflectionClass($trlRepeater);
    $itemDefinitions = $reflection->getProperty('itemDefinitions');
    $itemDefinitions->setAccessible(true);

    $itemDefinitionsValue = $itemDefinitions->getValue($trlRepeater);

    expect($itemDefinitionsValue['fields'][0]['type'])->toBe('trltext')
        ->and($itemDefinitionsValue['fields'][1]['type'])->toBe('trltextarea')
        ->and($itemDefinitionsValue['fields'][2]['type'])->toBe('dropdown');
});

it('processes save value correctly in TRLRepeater when locale is supported', function(): void {
    $this->trlRepeater->isSupported = true;

    mockRequest([
        'TRLTranslate' => ['testField' => ['fr' => ['name' => 'value']]],
    ]);

    $value = [
        'fr' => ['name' => 'defaultValue'],
    ];

    $result = $this->trlRepeater->getSaveValue($value);

    expect($result)->toBe([
        'fr' => ['name' => 'value'],
    ]);
});

it('processes save value correctly in TRLRepeater when locale is not supported', function(): void {
    $this->trlRepeater->isSupported = false;

    $value = [
        ['name' => 'defaultValue'],
    ];

    $result = $this->trlRepeater->getSaveValue($value);

    expect($result)->toBe($value);
});

it('returns locale save value correctly when model has specific mutate method', function(): void {
    mockRequest([
        'TRLTranslate' => ['en' => ['fieldName' => 'value']],
    ]);

    $this->model->shouldReceive('methodExists')->with('setFieldNameAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('setFieldNameAttributeTranslatedValue', ['value', 'en']);

    $result = $this->trlRepeater->getLocaleSaveValue('defaultValue', 'fieldName');

    expect($result)->toBe('defaultValue');
});

it('returns locale save value correctly when model has generic mutate method', function(): void {
    mockRequest([
        'TRLTranslate' => ['en' => ['fieldName' => 'value']],
    ]);

    $this->model->shouldReceive('methodExists')->with('setFieldNameAttributeTranslatedValue')->andReturn(false);
    $this->model->shouldReceive('methodExists')->with('setAttributeTranslatedValue')->andReturn(true);
    $this->model->shouldReceive('extendableCall')->with('setAttributeTranslatedValue', ['fieldName', 'value', 'en']);

    $result = $this->trlRepeater->getLocaleSaveValue('defaultValue', 'fieldName');

    expect($result)->toBe('defaultValue');
});

it('returns locale save value correctly in TRLRepeater', function(): void {
    mockRequest([
        'TRLTranslate' => ['en' => ['name' => 'value']],
    ]);

    $result = $this->trlRepeater->getLocaleSaveValue('defaultValue', 'name');

    expect($result)->toBe('defaultValue');
});

it('returns locale save data correctly in TRLRepeater', function(): void {
    mockRequest([
        'TRLTranslate' => ['en' => ['fieldName' => 'value']],
    ]);

    $result = $this->trlRepeater->getLocaleSaveData('fieldName');

    expect($result)->toBe(['en' => 'value']);
});

it('returns empty array when locale save data is not an array', function(): void {
    mockRequest([
        'TRLTranslate' => 'not an array',
    ]);

    $result = $this->trlRepeater->getLocaleSaveData('fieldName');

    expect($result)->toBe([]);
});

it('returns empty array when locale save data is empty', function(): void {
    mockRequest([
        'TRLTranslate' => [],
    ]);

    $result = $this->trlRepeater->getLocaleSaveData(null);

    expect($result)->toBe([]);
});
