<?php namespace Igniter\Translate\Classes;

use Admin\Models\Menu_option_values_model;
use Admin\Models\Menu_options_model;
use Igniter\Flame\Traits\Singleton;
use Igniter\Pages\Classes\Page as StaticPage;
use Main\Template\Page as ThemePage;
use System\Models\Mail_templates_model;

class EventRegistry
{
    use Singleton;

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

        if (!$model->isClassExtendedWith('Igniter\Translate\Actions\TranslatableModel'))
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

        if ($model instanceof ThemePage AND isset($widget->fields['settings[url]'])) {
            $widget->fields['settings[url]']['type'] = 'trltext';
        }
        elseif ($model instanceof StaticPage AND isset($widget->fields['viewBag[url]'])) {
            $widget->fields['viewBag[url]']['type'] = 'trltext';
        }
    }

    public function bootTranslatableModels()
    {
        Menu_options_model::extend(function ($model) {
            $model->implement[] = 'Igniter\Translate\Actions\TranslatableModel';
            $model->addDynamicProperty('translatable', ['option_name', 'option_values']);
        });

        Menu_option_values_model::extend(function ($model) {
            $model->implement[] = 'Igniter\Translate\Actions\TranslatableModel';
            $model->addDynamicProperty('translatable', ['value']);
        });

        Mail_templates_model::extend(function ($model) {
            $model->implement[] = 'Igniter\Translate\Actions\TranslatableModel';
            $model->addDynamicProperty('translatable', ['subject', 'body']);
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