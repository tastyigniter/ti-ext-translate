<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\Actions;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Translation\Localization;
use Igniter\Translate\Actions\TranslatableAction;
use Mockery;
use ReflectionClass;

beforeEach(function(): void {
    $this->model = Mockery::mock(Model::class)->makePartial();
    $this->localization = Mockery::mock(Localization::class);
    app()->instance('translator.localization', $this->localization);
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $this->model->shouldReceive('bindEvent')->with('model.beforeGetAttribute', Mockery::any())->once();
    $this->model->shouldReceive('bindEvent')->with('model.beforeSetAttribute', Mockery::any())->once();
    $this->model->shouldReceive('bindEvent')->with('model.saveInternal', Mockery::any())->once();
    $this->localization->shouldReceive('getLocale')->andReturn('fr');
    $this->localization->shouldReceive('getDefaultLocale')->andReturn('en');

    $this->translatableAction = new class($this->model) extends TranslatableAction
    {
        protected function storeTranslatableAttributes($locale = null): void {}

        protected function loadTranslatableAttributes($locale = null): void
        {
            $this->translatableAttributes = [
                'en' => ['name' => 'original_name'],
                'fr' => ['name' => 'translated_name'],
            ];
        }
    };
});

it('initializes translatable locale correctly', function(): void {
    $model = Mockery::mock(Model::class)->makePartial();
    $localization = Mockery::mock(Localization::class);
    app()->instance('translator.localization', $localization);

    $localization->shouldReceive('getLocale')->andReturn('fr');
    $localization->shouldReceive('getDefaultLocale')->andReturn('en');
    $model->shouldReceive('getOriginal')->andReturn(['name' => 'original_name'])->once();
    $model->shouldReceive('getAttributes')->andReturn(['name' => 'current_name'])->atMost(3);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $model->shouldReceive('setRawAttributes')->once();
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $model->shouldReceive('bindEvent')->with('model.beforeGetAttribute', Mockery::on(function($callback): true {
        $callback('name');

        return true;
    }))->twice();
    $model->shouldReceive('bindEvent')->with('model.beforeSetAttribute', Mockery::on(function($callback): true {
        $callback('name', ['en' => 'translated_name']);

        return true;
    }))->twice();
    $model->shouldReceive('bindEvent')->with('model.saveInternal', Mockery::on(function($callback): true {
        $callback();

        return true;
    }))->twice();

    $translatableAction = new class($model) extends TranslatableAction
    {
        protected function storeTranslatableAttributes($locale = null): void {}

        protected function loadTranslatableAttributes($locale = null): void
        {
            $this->translatableAttributes = [
                'en' => ['name' => 'original_name'],
                'fr' => ['name' => 'translated_name'],
            ];
        }
    };

    $reflection = new ReflectionClass($translatableAction);
    $translatableActiveLocale = $reflection->getProperty('translatableActiveLocale');
    $translatableActiveLocale->setAccessible(true);

    $translatableDefaultLocale = $reflection->getProperty('translatableDefaultLocale');
    $translatableDefaultLocale->setAccessible(true);

    expect($translatableActiveLocale->getValue($translatableAction))->toBe('fr')
        ->and($translatableDefaultLocale->getValue($translatableAction))->toBe('en');

    $localization = Mockery::mock(Localization::class);
    $localization->shouldReceive('getLocale')->andReturn('en');
    $localization->shouldReceive('getDefaultLocale')->andReturn('en');
    app()->instance('translator.localization', $localization);
    new class($model) extends TranslatableAction
    {
        protected function storeTranslatableAttributes($locale = null): void {}

        protected function loadTranslatableAttributes($locale = null): void
        {
            $this->translatableAttributes = [
                'en' => ['name' => 'original_name'],
                'fr' => ['name' => 'translated_name'],
            ];
        }
    };
});

it('gets translated attribute value with mutator', function(): void {
    $this->model->shouldReceive('getAttributes')->andReturn(['name' => 'default_name']);
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $this->model->shouldReceive('hasGetMutator')->with('name')->andReturn(true);
    $this->model->shouldReceive('getNameAttribute')->andReturn('mutated_name');

    $value = $this->translatableAction->performGetTranslatableAttribute('name');

    expect($value)->toBe('mutated_name');
});

it('sets translated attribute value with mutator', function(): void {
    $this->model->shouldReceive('getAttributes')->andReturn(['name' => 'default_name']);
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $this->model->shouldReceive('hasSetMutator')->with('name')->andReturn(true);
    $this->model->shouldReceive('setNameAttribute')->andReturn('mutated_name');

    $this->translatableAction->setAttributeTranslatedValue('name', 'new_translated_name');
    $value = $this->translatableAction->performSetTranslatableAttribute('name', 'translated_name');

    expect($value)->toBe('mutated_name');
});

it('syncs translatable attributes correctly', function(): void {
    $this->model->shouldReceive('getOriginal')->andReturn(['name' => 'original_name'])->once();
    $this->model->shouldReceive('getAttributes')->andReturn(['name' => 'current_name'])->once();
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);
    $this->model->shouldReceive('setRawAttributes')->once();

    $reflection = new ReflectionClass($this->translatableAction);
    $reflection2 = new ReflectionClass($this->translatableAction);
    $translatableAttributes = $reflection->getProperty('translatableAttributes');
    $translatableAttributes->setValue($this->translatableAction, ['fr' => ['name' => 'translated_name']]);

    $translatableOriginals = $reflection2->getProperty('translatableOriginals');
    $translatableOriginals->setValue($this->translatableAction, ['fr' => ['name' => 'original_name']]);

    $this->translatableAction->syncTranslatableAttributes();
});

it('sets active locale correctly', function(): void {
    $this->translatableAction->translatableSetActiveLocale('fr');

    $reflection = new ReflectionClass($this->translatableAction);
    $translatableActiveLocale = $reflection->getProperty('translatableActiveLocale');
    $translatableActiveLocale->setAccessible(true);

    expect($translatableActiveLocale->getValue($this->translatableAction))->toBe('fr');
});

it('gets translated attribute value correctly', function(): void {
    $this->model->shouldReceive('getAttributes')->andReturn(['name' => 'default_name']);
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);

    expect($this->translatableAction->getAttributeTranslatedValue('name'))->toBe('translated_name')
        ->and($this->translatableAction->getAttributeTranslatedValue('name', 'en'))->toBe('default_name')
        ->and($this->translatableAction->getAttributeTranslatedValue('not_found'))->toBeNull();
});

it('sets translated attribute value correctly', function(): void {
    $this->model->shouldReceive('getAttributes')->andReturn(['name' => 'default_name']);

    $this->translatableAction->setAttributeTranslatedValue('name', 'new_translated_name');

    $reflection = new ReflectionClass($this->translatableAction);
    $translatableAttributes = $reflection->getProperty('translatableAttributes');
    $translatableAttributes->setAccessible(true);

    $translatableAttributesValue = $translatableAttributes->getValue($this->translatableAction);

    expect($translatableAttributesValue['fr']['name'])->toBe('new_translated_name')
        ->and($this->translatableAction->setAttributeTranslatedValue('name', 'new_translated_name', 'en'))
        ->toBe('new_translated_name');

});

it('checks if attribute is translatable', function(): void {
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);

    $isTranslatable = $this->translatableAction->isTranslatableAttribute('name');

    expect($isTranslatable)->toBeTrue();
});

it('checks if translatable attribute is dirty', function(): void {
    $this->model->shouldReceive('getTranslatableAttributes')->andReturn(['name']);

    $reflection = new ReflectionClass($this->translatableAction);
    $reflection2 = new ReflectionClass($this->translatableAction);
    $translatableAttributes = $reflection->getProperty('translatableAttributes');
    $translatableAttributes->setValue($this->translatableAction, ['fr' => ['name' => 'translated_name']]);

    $translatableOriginals = $reflection2->getProperty('translatableOriginals');
    $translatableOriginals->setValue($this->translatableAction, ['fr' => ['name' => 'original_name']]);

    expect($this->translatableAction->isTranslatableDirty('name', 'fr'))->toBeTrue();

    $translatableAttributes->setValue($this->translatableAction, []);
    expect($this->translatableAction->isTranslatableDirty('name', 'fr'))->toBeFalse();

    $translatableAttributes->setValue($this->translatableAction, ['fr' => ['name' => 'translated_name']]);
    $translatableOriginals->setValue($this->translatableAction, []);
    expect($this->translatableAction->isTranslatableDirty('name', 'fr'))->toBeTrue();

    $translatableAttributes->setValue($this->translatableAction, ['fr' => ['name' => 'translated_name']]);
    $translatableOriginals->setValue($this->translatableAction, ['fr' => []]);
    expect($this->translatableAction->isTranslatableDirty('name', 'fr'))->toBeTrue();
});

it('disables translation fallback locale', function(): void {
    $result = $this->translatableAction->translatableNoFallbackLocale();

    $reflection = new ReflectionClass($this->translatableAction);
    $translatableUseFallback = $reflection->getProperty('translatableUseFallback');
    $translatableUseFallback->setAccessible(true);

    expect($translatableUseFallback->getValue($this->translatableAction))->toBeFalse()
        ->and($result)->toBeInstanceOf(Model::class);
});

it('returns true when translation exists for non-active locale', function(): void {
    $result = $this->translatableAction->hasTranslation('name', 'en');

    expect($result)->toBeTrue();
});

it('returns translatable attributes when model has translatable attributes', function(): void {
    $this->model->shouldReceive('translatable')->andReturn(['name', 'description']);

    expect($this->translatableAction->getTranslatableAttributes())->toBe(['name', 'description']);
});

it('returns empty array when model has no translatable attributes', function(): void {
    $this->model->shouldReceive('translatable')->andReturn(null);

    expect($this->translatableAction->getTranslatableAttributes())->toBeEmpty();
});

it('returns true when model has translatable attributes', function(): void {
    $this->model->shouldReceive('translatable')->andReturn(['name', 'description']);

    expect($this->translatableAction->hasTranslatableAttributes())->toBeTrue();
});

it('returns false when model has no translatable attributes', function(): void {
    $this->model->shouldReceive('translatable')->andReturn(null);

    expect($this->translatableAction->hasTranslatableAttributes())->toBeFalse();
});

it('extends functionality with callback', function(): void {
    $callback = fn(): string => 'extended';

    expect(TranslatableAction::extend($callback))->toBeNull();
});
