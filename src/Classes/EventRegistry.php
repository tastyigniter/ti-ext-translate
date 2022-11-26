<?php namespace Igniter\Translate\Classes;

use Igniter\Admin\Models\Category;
use Igniter\Admin\Models\Ingredient;
use Igniter\Admin\Models\Menu;
use Igniter\Admin\Models\MenuOption;
use Igniter\Admin\Models\MenuOptionValue;
use Igniter\Main\Template\Page as ThemePage;
use Igniter\Pages\Classes\Page as StaticPage;
use Igniter\Pages\Models\MenuItem;
use Igniter\Pages\Models\Page;
use Igniter\System\Models\MailTemplate;

class EventRegistry
{
    public function registerFormFieldReplacements($widget)
    {
        $this->registerModelTranslatableFields($widget);

        $this->registerPageTranslatableFields($widget);
    }

    public function registerModelTranslatableFields($widget)
    {
        if (!$model = $widget->model)
            return;

        if (!method_exists($model, 'isClassExtendedWith'))
            return;

        if (!$model->isClassExtendedWith(\Igniter\Translate\Actions\TranslatableModel::class))
            return;

        if (!$model->hasTranslatableAttributes())
            return;

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

    public function registerPageTranslatableFields($widget)
    {
        if (!$model = $widget->model)
            return;

        if ($model instanceof ThemePage && isset($widget->fields['settings[url]'])) {
            $widget->fields['settings[url]']['type'] = 'trltext';
        }
        elseif ($model instanceof StaticPage && isset($widget->fields['viewBag[url]'])) {
            $widget->fields['viewBag[url]']['type'] = 'trltext';
        }
    }

    public function bootTranslatableModels()
    {
        Ingredient::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['name', 'description']);
        });

        Category::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['name', 'description']);
        });

        MailTemplate::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['subject', 'body']);
        });

        MenuOption::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['option_name', 'option_values']);
        });

        MenuOptionValue::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['value']);
        });

        Menu::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['menu_name', 'menu_description']);
        });

        MenuItem::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['title', 'description']);
        });

        Page::extend(function ($model) {
            $model->implement[] = \Igniter\Translate\Actions\TranslatableModel::class;
            $model->addDynamicProperty('translatable', ['title', 'content', 'meta_description', 'meta_keywords']);
        });
    }

    //
    // Helpers
    //

    protected function processTranslatableFormFields($fields, $translatable)
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

            if (in_array($type, $translatableFields))
                $fields[$name]['type'] = 'trl'.$type;
        }

        return $fields;
    }
}
