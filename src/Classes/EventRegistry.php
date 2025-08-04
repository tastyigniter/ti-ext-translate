<?php

declare(strict_types=1);

namespace Igniter\Translate\Classes;

use Igniter\Admin\Widgets\Form;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Ingredient;
use Igniter\Cart\Models\Mealtime;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOption;
use Igniter\Cart\Models\MenuOptionValue;
use Igniter\Flame\Database\Model;
use Igniter\Local\Models\Location;
use Igniter\Main\Template\Page as ThemePage;
use Igniter\Pages\Classes\Page as StaticPage;
use Igniter\Pages\Models\Menu as StaticPageMenu;
use Igniter\Pages\Models\MenuItem as StaticPageMenuItem;
use Igniter\Pages\Models\Page;
use Igniter\System\Models\MailTemplate;
use Igniter\Translate\Actions\TranslatableModel;

class EventRegistry
{
    public function registerFormFieldReplacements(Form $widget): void
    {
        $this->registerModelTranslatableFields($widget);

        $this->registerPageTranslatableFields($widget);
    }

    public function registerModelTranslatableFields(Form $widget): void
    {
        /** @var null|Model|TranslatableModel $model */
        $model = $widget->model;
        if (
            !$model
            || !method_exists($model, 'isClassExtendedWith')
            || !$model->isClassExtendedWith(TranslatableModel::class)
            || !$model->hasTranslatableAttributes()
        ) {
            return;
        }

        $translatable = array_flip($model->getTranslatableAttributes());

        if (!empty($widget->config['fields'])) {
            $widget->fields = $this->processTranslatableFormFields($widget->fields, $translatable);
        }

        if (!empty($widget->config['tabs']['fields'])) {
            $widget->tabs['fields'] = $this->processTranslatableFormFields($widget->tabs['fields'], $translatable);
        }

        if (!empty($widget->config['secondaryTabs']['fields'])) {
            $widget->secondaryTabs['fields'] = $this->processTranslatableFormFields($widget->secondaryTabs['fields'], $translatable);
        }
    }

    public function registerPageTranslatableFields(Form $widget): void
    {
        if (($model = $widget->model) === null) {
            return;
        }

        if ($model instanceof ThemePage && isset($widget->fields['settings[url]'])) {
            $widget->fields['settings[url]']['type'] = 'trltext';
        } elseif ($model instanceof StaticPage && isset($widget->fields['viewBag[url]'])) {
            $widget->fields['viewBag[url]']['type'] = 'trltext';
        }
    }

    public function bootTranslatableModels(): void
    {
        Mealtime::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['mealtime_name']);
        });

        Ingredient::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['name', 'description']);
        });

        Category::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['name', 'description']);
        });

        Location::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['location_name', 'description']);
        });

        MailTemplate::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['subject', 'body']);
        });

        MenuOption::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['option_name', 'values']);
        });

        MenuOptionValue::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['name']);
        });

        Menu::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['menu_name', 'menu_description']);
        });

        StaticPageMenu::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['name']);
        });

        StaticPageMenuItem::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['title', 'description']);
        });

        Page::extend(function($model): void {
            $model->implement[] = TranslatableModel::class;
            $model->addDynamicMethod('translatable', fn(): array => ['title', 'content', 'meta_description', 'meta_keywords']);
        });
    }

    //
    // Helpers
    //

    protected function processTranslatableFormFields(array $fields, $translatable): array
    {
        foreach ($fields as $name => $config) {
            if (!array_key_exists($name, $translatable)) {
                continue;
            }

            $type = array_get($config, 'type', 'text');

            $translatableFields = [
                'text',
                'textarea',
                'markdowneditor',
                'richeditor',
                'repeater',
            ];

            if (in_array($type, $translatableFields)) {
                $fields[$name]['type'] = 'trl'.$type;
            }
        }

        return $fields;
    }
}
