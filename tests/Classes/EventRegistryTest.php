<?php

namespace Igniter\Translate\Tests\Classes;

use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Ingredient;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Local\Models\Location;
use Igniter\Pages\Classes\Page;
use Igniter\Pages\Models\MenuItem;
use Igniter\System\Models\MailTemplate;
use Igniter\Translate\Actions\TranslatableModel;
use Igniter\Translate\Classes\EventRegistry;
use Mockery;

it('registers form field replacements for model with translatable attributes', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(true);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['name', 'description']);
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->config = ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]];
    $widget->fields = $widget->config['fields'];

    (new EventRegistry())->registerFormFieldReplacements($widget);

    expect($widget->fields['name']['type'])->toBe('trltext')
        ->and($widget->fields['description']['type'])->toBe('trltextarea');
});

it('registers form field replacements for ThemePage model', function() {
    $model = Mockery::mock(\Igniter\Main\Template\Page::class);
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(false);
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->fields = ['settings[url]' => ['type' => 'text']];

    (new EventRegistry())->registerFormFieldReplacements($widget);

    expect($widget->fields['settings[url]']['type'])->toBe('trltext');
});

it('registers form field replacements for StaticPage model', function() {
    $model = Mockery::mock(Page::class)->makePartial();
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->fields = ['viewBag[url]' => ['type' => 'text']];

    (new EventRegistry())->registerFormFieldReplacements($widget);

    expect($widget->fields['viewBag[url]']['type'])->toBe('trltext');
});

it('does not register form field replacements for model without translatable attributes', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(false);
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->config = ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]];
    $widget->fields = $widget->config['fields'];

    (new EventRegistry())->registerFormFieldReplacements($widget);

    expect($widget->fields['name']['type'])->toBe('text')
        ->and($widget->fields['description']['type'])->toBe('textarea');
});

it('does not register form field replacements for non-ThemePage and non-StaticPage models', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->fields = ['viewBag[url]' => ['type' => 'text']];

    (new EventRegistry())->registerFormFieldReplacements($widget);

    expect($widget->fields['viewBag[url]']['type'])->toBe('text');
});

it('registers translatable fields for model with translatable attributes', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(true);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['name', 'description']);
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->config = [
        'fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']],
        'tabs' => ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]],
        'secondaryTabs' => ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]],
    ];
    $widget->fields = $widget->config['fields'];
    $widget->tabs = $widget->config['tabs'];
    $widget->secondaryTabs = $widget->config['secondaryTabs'];

    $eventRegistry = new EventRegistry();
    $eventRegistry->registerModelTranslatableFields($widget);

    expect($widget->fields['name']['type'])->toBe('trltext')
        ->and($widget->fields['description']['type'])->toBe('trltextarea');
});

it('skips translatable fields for model without translatable attributes', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(true);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['not_description']);
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->config = ['fields' => ['description' => ['type' => 'textarea']]];
    $widget->fields = $widget->config['fields'];

    $eventRegistry = new EventRegistry();
    $eventRegistry->registerModelTranslatableFields($widget);

    expect($widget->fields)->not->toHaveKey('name')
        ->and($widget->fields['description']['type'])->toBe('textarea');
});

it('does not register translatable fields for model without translatable attributes', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true)->once();
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(false)->once();
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->config = ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]];
    $widget->fields = $widget->config['fields'];

    $eventRegistry = new EventRegistry();
    $eventRegistry->registerModelTranslatableFields($widget);

    expect($widget->fields['name']['type'])->toBe('text')
        ->and($widget->fields['description']['type'])->toBe('textarea');
});

it('registers translatable fields for ThemePage model', function() {
    $model = Mockery::mock(Page::class)->makePartial();
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->fields = ['settings[url]' => ['type' => 'text']];

    $eventRegistry = new EventRegistry();
    $eventRegistry->registerPageTranslatableFields($widget);

    expect($widget->fields['settings[url]']['type'])->toBe('trltext');

    $widget->model = null;
    expect($eventRegistry->registerPageTranslatableFields($widget))->toBeNull();
});

it('registers translatable fields for StaticPage model', function() {
    $model = Mockery::mock(Page::class);
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->fields = ['viewBag[url]' => ['type' => 'text']];

    $eventRegistry = new EventRegistry();
    $eventRegistry->registerPageTranslatableFields($widget);

    expect($widget->fields['viewBag[url]']['type'])->toBe('trltext');
});

it('does not register translatable fields for non-ThemePage and non-StaticPage models', function() {
    $model = Mockery::mock(Category::class)->makePartial();
    $widget = Mockery::mock('stdClass');
    $widget->model = $model;
    $widget->fields = ['viewBag[url]' => ['type' => 'text']];

    $eventRegistry = new EventRegistry();
    $eventRegistry->registerPageTranslatableFields($widget);

    expect($widget->fields['viewBag[url]']['type'])->toBe('text');
});

it('extends models with translatable attributes', function() {
    (new EventRegistry())->bootTranslatableModels();

    $ingredient = new Ingredient();
    $category = new Category();
    $location = new Location();
    $mailTemplate = new MailTemplate();
    $menuOption = new MenuOption();
    $menuOptionValue = new MenuOptionValue();
    $menu = new Menu();
    $menuItem = new MenuItem();
    $pages = new \Igniter\Pages\Models\Page();

    expect($ingredient->implement)->toContain(TranslatableModel::class)
        ->and($ingredient->translatable())->toBe(['name', 'description'])
        ->and($category->implement)->toContain(TranslatableModel::class)
        ->and($category->translatable())->toBe(['name', 'description'])
        ->and($location->implement)->toContain(TranslatableModel::class)
        ->and($location->translatable())->toBe(['location_name', 'description'])
        ->and($mailTemplate->implement)->toContain(TranslatableModel::class)
        ->and($mailTemplate->translatable())->toBe(['subject', 'body'])
        ->and($menuOption->implement)->toContain(TranslatableModel::class)
        ->and($menuOption->translatable())->toBe(['option_name', 'option_values'])
        ->and($menuOptionValue->implement)->toContain(TranslatableModel::class)
        ->and($menuOptionValue->translatable())->toBe(['value'])
        ->and($menu->implement)->toContain(TranslatableModel::class)
        ->and($menu->translatable())->toBe(['menu_name', 'menu_description'])
        ->and($menuItem->implement)->toContain(TranslatableModel::class)
        ->and($menuItem->translatable())->toBe(['title', 'description'])
        ->and($pages->implement)->toContain(TranslatableModel::class)
        ->and($pages->translatable())->toBe(['title', 'content', 'meta_description', 'meta_keywords']);
});
