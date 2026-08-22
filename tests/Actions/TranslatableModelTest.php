<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\Actions;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Translation\Localization;
use Igniter\Translate\Actions\TranslatableModel;
use Igniter\Translate\Models\Attribute;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;
use ReflectionClass;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->localization = Mockery::mock(Localization::class);
    app()->instance('translator.localization', $this->localization);
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $this->localization->shouldReceive('getLocale')->andReturn('fr');
    $this->localization->shouldReceive('getDefaultLocale')->andReturn('en');

    $this->translatableModel = new TranslatableModel($this->model);

    $reflection = new ReflectionClass($this->translatableModel);
    $translatableAttributes = $reflection->getProperty('translatableAttributes');
    $translatableAttributes->setValue($this->translatableModel, [
        'en' => ['name' => 'original_name'],
        'fr' => ['name' => 'translated_name'],
    ]);
});

it('stores translatable attributes for active locale when model exists', function(): void {
    $this->model->exists = true;
    $this->model->shouldReceive('getKey')->andReturn(1);
    $this->model->shouldReceive('getMorphClass')->andReturn('morphClass');
    $this->model->shouldReceive('translations')->andReturn(collect());

    $reflection = new ReflectionClass($this->translatableModel);
    $method = $reflection->getMethod('storeTranslatableAttributes');
    $method->invoke($this->translatableModel);

    expect(Attribute::where([
        'locale' => 'fr',
        'translatable_id' => 1,
        'translatable_type' => 'morphClass',
    ])->first()->attribute)->toBe('{"name":"translated_name"}');
});

it('stores translatable attributes for specified locale when model exists', function(): void {
    $this->model->exists = true;
    $this->model->shouldReceive('getKey')->andReturn(1);
    $this->model->shouldReceive('getMorphClass')->andReturn('morphClass');

    $reflection = new ReflectionClass($this->translatableModel);
    $method = $reflection->getMethod('storeTranslatableAttributes');
    $method->invoke($this->translatableModel, 'fr');

    expect(Attribute::where([
        'locale' => 'fr',
        'translatable_id' => 1,
        'translatable_type' => 'morphClass',
    ])->first())->not->toBeNull();
});

it('binds event to store translatable attributes after model creation', function(): void {
    $this->model->exists = false;
    $this->model->shouldReceive('getMorphClass')->andReturn('morphClass');
    $this->model->shouldReceive('bindEventOnce')->with('model.afterCreate', Mockery::on(function($callback): true {
        $this->model->exists = true;
        $callback();

        return true;
    }))->once();

    $reflection = new ReflectionClass($this->translatableModel);
    $translatableActiveLocale = $reflection->getProperty('translatableActiveLocale');
    $translatableActiveLocale->setValue($this->translatableModel, 'en');

    $method = $reflection->getMethod('storeTranslatableAttributes');
    $method->invoke($this->translatableModel);
});

it('loads translatable attributes for active locale when model exists', function(): void {
    $this->model->exists = true;
    $this->model->shouldReceive('getKey')->andReturn(1);
    $this->model->shouldReceive('getMorphClass')->andReturn('morphClass');

    Attribute::query()->create([
        'locale' => 'en',
        'translatable_id' => 1,
        'translatable_type' => 'morphClass',
        'attribute' => json_encode(['name' => 'translated_name']),
    ]);

    $reflection = new ReflectionClass($this->translatableModel);
    $translatableActiveLocale = $reflection->getProperty('translatableActiveLocale');
    $translatableActiveLocale->setValue($this->translatableModel, 'en');

    $method = $reflection->getMethod('loadTranslatableAttributes');

    $result = $method->invoke($this->translatableModel);

    expect($result)->toBe(['name' => 'translated_name']);
});

it('loads only this model translations when eloquent relation constraints are disabled', function(): void {
    $this->model->exists = true;
    $this->model->shouldReceive('getKey')->andReturn(2);
    $this->model->shouldReceive('getMorphClass')->andReturn('locations');

    Attribute::query()->create([
        'locale' => 'en',
        'translatable_id' => 1,
        'translatable_type' => 'locations',
        'attribute' => json_encode(['name' => 'Default']),
    ]);
    Attribute::query()->create([
        'locale' => 'en',
        'translatable_id' => 2,
        'translatable_type' => 'locations',
        'attribute' => json_encode(['name' => 'Norman Foremann']),
    ]);

    $reflection = new ReflectionClass($this->translatableModel);
    $method = $reflection->getMethod('loadTranslatableAttributes');

    $result = Relation::noConstraints(fn(): mixed => $method->invoke($this->translatableModel, 'en'));

    expect($result)->toBe(['name' => 'Norman Foremann']);
});

it('loads empty translatable attributes for active locale when model does not exist', closure: function(): void {
    $this->model->exists = false;
    $reflection = new ReflectionClass($this->translatableModel);
    $translatableActiveLocale = $reflection->getProperty('translatableActiveLocale');
    $translatableActiveLocale->setValue($this->translatableModel, 'en');

    $method = $reflection->getMethod('loadTranslatableAttributes');

    $result = $method->invoke($this->translatableModel);

    expect($result)->toBe([]);
});
