<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\Classes;

use Igniter\Admin\Widgets\Form;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Ingredient;
use Igniter\Cart\Models\Mealtime;
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

beforeEach(function(): void {
    $this->formWidget = new class extends Form
    {
        public function __construct() {}
    };
});

it('registers form field replacements for model with translatable attributes', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(true);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['name', 'description']);
    $this->formWidget->model = $model;
    $this->formWidget->config = ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]];
    $this->formWidget->fields = $this->formWidget->config['fields'];

    (new EventRegistry)->registerFormFieldReplacements($this->formWidget);

    expect($this->formWidget->fields['name']['type'])->toBe('trltext')
        ->and($this->formWidget->fields['description']['type'])->toBe('trltextarea');
});

it('registers form field replacements for ThemePage model', function(): void {
    $model = Mockery::mock(\Igniter\Main\Template\Page::class);
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(false);
    $this->formWidget->model = $model;
    $this->formWidget->fields = ['settings[url]' => ['type' => 'text']];

    (new EventRegistry)->registerFormFieldReplacements($this->formWidget);

    expect($this->formWidget->fields['settings[url]']['type'])->toBe('trltext');
});

it('registers form field replacements for StaticPage model', function(): void {
    $model = Mockery::mock(Page::class)->makePartial();
    $this->formWidget->model = $model;

    $this->formWidget->fields = ['viewBag[url]' => ['type' => 'text']];

    (new EventRegistry)->registerFormFieldReplacements($this->formWidget);

    expect($this->formWidget->fields['viewBag[url]']['type'])->toBe('trltext');
});

it('does not register form field replacements for model without translatable attributes', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(false);
    $this->formWidget->model = $model;
    $this->formWidget->config = ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]];
    $this->formWidget->fields = $this->formWidget->config['fields'];

    (new EventRegistry)->registerFormFieldReplacements($this->formWidget);

    expect($this->formWidget->fields['name']['type'])->toBe('text')
        ->and($this->formWidget->fields['description']['type'])->toBe('textarea');
});

it('does not register form field replacements for non-ThemePage and non-StaticPage models', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $this->formWidget->model = $model;

    $this->formWidget->fields = ['viewBag[url]' => ['type' => 'text']];

    (new EventRegistry)->registerFormFieldReplacements($this->formWidget);

    expect($this->formWidget->fields['viewBag[url]']['type'])->toBe('text');
});

it('registers translatable fields for model with translatable attributes', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(true);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['name', 'description']);
    $this->formWidget->model = $model;
    $this->formWidget->config = [
        'fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']],
        'tabs' => ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]],
        'secondaryTabs' => ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]],
    ];
    $this->formWidget->fields = $this->formWidget->config['fields'];
    $this->formWidget->tabs = $this->formWidget->config['tabs'];
    $this->formWidget->secondaryTabs = $this->formWidget->config['secondaryTabs'];

    $eventRegistry = new EventRegistry;
    $eventRegistry->registerModelTranslatableFields($this->formWidget);

    expect($this->formWidget->fields['name']['type'])->toBe('trltext')
        ->and($this->formWidget->fields['description']['type'])->toBe('trltextarea');
});

it('skips translatable fields for model without translatable attributes', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true);
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(true);
    $model->shouldReceive('getTranslatableAttributes')->andReturn(['not_description']);
    $this->formWidget->model = $model;
    $this->formWidget->config = ['fields' => ['description' => ['type' => 'textarea']]];
    $this->formWidget->fields = $this->formWidget->config['fields'];

    $eventRegistry = new EventRegistry;
    $eventRegistry->registerModelTranslatableFields($this->formWidget);

    expect($this->formWidget->fields)->not->toHaveKey('name')
        ->and($this->formWidget->fields['description']['type'])->toBe('textarea');
});

it('does not register translatable fields for model without translatable attributes', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $model->shouldReceive('isClassExtendedWith')->andReturn(true)->once();
    $model->shouldReceive('hasTranslatableAttributes')->andReturn(false)->once();
    $this->formWidget->model = $model;
    $this->formWidget->config = ['fields' => ['name' => ['type' => 'text'], 'description' => ['type' => 'textarea']]];
    $this->formWidget->fields = $this->formWidget->config['fields'];

    $eventRegistry = new EventRegistry;
    $eventRegistry->registerModelTranslatableFields($this->formWidget);

    expect($this->formWidget->fields['name']['type'])->toBe('text')
        ->and($this->formWidget->fields['description']['type'])->toBe('textarea');
});

it('registers translatable fields for ThemePage model', function(): void {
    $model = Mockery::mock(Page::class)->makePartial();
    $this->formWidget->model = $model;

    $this->formWidget->fields = ['settings[url]' => ['type' => 'text']];

    $eventRegistry = new EventRegistry;
    $eventRegistry->registerPageTranslatableFields($this->formWidget);

    expect($this->formWidget->fields['settings[url]']['type'])->toBe('trltext');

    $this->formWidget->model = null;
    expect($eventRegistry->registerPageTranslatableFields($this->formWidget))->toBeNull();
});

it('registers translatable fields for StaticPage model', function(): void {
    $model = Mockery::mock(Page::class);
    $this->formWidget->model = $model;

    $this->formWidget->fields = ['viewBag[url]' => ['type' => 'text']];

    $eventRegistry = new EventRegistry;
    $eventRegistry->registerPageTranslatableFields($this->formWidget);

    expect($this->formWidget->fields['viewBag[url]']['type'])->toBe('trltext');
});

it('does not register translatable fields for non-ThemePage and non-StaticPage models', function(): void {
    $model = Mockery::mock(Category::class)->makePartial();
    $this->formWidget->model = $model;

    $this->formWidget->fields = ['viewBag[url]' => ['type' => 'text']];

    $eventRegistry = new EventRegistry;
    $eventRegistry->registerPageTranslatableFields($this->formWidget);

    expect($this->formWidget->fields['viewBag[url]']['type'])->toBe('text');
});

it('extends models with translatable attributes', function(): void {
    (new EventRegistry)->bootTranslatableModels();

    $ingredient = new Ingredient;
    $category = new Category;
    $location = new Location;
    $mailTemplate = new MailTemplate;
    $mealtime = new Mealtime;
    $menuOption = new MenuOption;
    $menuOptionValue = new MenuOptionValue;
    $menu = new Menu;
    $menuItem = new MenuItem;
    $pages = new \Igniter\Pages\Models\Page;

    expect($ingredient->implement)->toContain(TranslatableModel::class)
        ->and($ingredient->translatable())->toBe(['name', 'description'])
        ->and($category->implement)->toContain(TranslatableModel::class)
        ->and($category->translatable())->toBe(['name', 'description'])
        ->and($location->implement)->toContain(TranslatableModel::class)
        ->and($location->translatable())->toBe(['location_name', 'description'])
        ->and($mailTemplate->implement)->toContain(TranslatableModel::class)
        ->and($mailTemplate->translatable())->toBe(['subject', 'body'])
        ->and($menuOption->implement)->toContain(TranslatableModel::class)
        ->and($menuOption->translatable())->toBe(['option_name', 'values'])
        ->and($mealtime->implement)->toContain(TranslatableModel::class)
        ->and($mealtime->translatable())->toBe(['mealtime_name'])
        ->and($menuOptionValue->implement)->toContain(TranslatableModel::class)
        ->and($menuOptionValue->translatable())->toBe(['name'])
        ->and($menu->implement)->toContain(TranslatableModel::class)
        ->and($menu->translatable())->toBe(['menu_name', 'menu_description'])
        ->and($menuItem->implement)->toContain(TranslatableModel::class)
        ->and($menuItem->translatable())->toBe(['title', 'description'])
        ->and($pages->implement)->toContain(TranslatableModel::class)
        ->and($pages->translatable())->toBe(['title', 'content', 'meta_description', 'meta_keywords']);
});
