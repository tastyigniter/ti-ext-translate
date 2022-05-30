<?php

namespace Igniter\Translate\FormWidgets;

use Admin\FormWidgets\Repeater;
use Igniter\Flame\Support\Str;
use System\Models\Languages_model;

class TRLRepeater extends Repeater
{
    use \Igniter\Translate\FormWidgets\TRLBase;

    protected $defaultAlias = 'trlrepeater';

    public function __construct($controller, $formField, $configuration = [])
    {
        $this->parentPartialPath[] = '~/app/admin/formwidgets';
        $this->parentPartialPath[] = '~/app/admin/formwidgets/repeater';
        $this->parentAssetPath[] = '~/app/admin/formwidgets/repeater/assets';

        parent::__construct($controller, $formField, $configuration);
//        $this->partialPath[] = '~/app/admin/formwidgets';
    }

    public function initialize()
    {
        parent::initialize();

        $this->initLocale();
    }

    public function render()
    {
        $parentContent = $this->maskAsParent(function () {
            return parent::render();
        });

        if (!$this->isSupported)
            return $parentContent;

        $this->vars['repeater'] = $parentContent;

        return $this->makePartial('trlrepeater/trlrepeater');
    }

    public function prepareVars()
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    public function loadAssets()
    {
        $this->maskAsParent(function () {
            parent::loadAssets();
        });

        if (Languages_model::supportsLocale()) {
            $this->loadLocaleAssets();
            $this->addJs('js/trlrepeater.js');
        }
    }

    protected function processItemDefinitions()
    {
        parent::processItemDefinitions();

        $translatableFields = [
            'text',
            'textarea',
        ];

        foreach ($this->itemDefinitions['fields'] as &$field) {
            if (in_array($field['type'], $translatableFields)) {
                $field['type'] = 'trl'.$field['type'];
                $field['controlType'] = 'translatable-repeater';
                $field['attributes'] = array_merge($field['attributes'] ?? [], [
                    'data-translatable-repeater' => implode('.', name_to_array($this->fieldName)),
                ]);
            }
        }
    }

    protected function processSaveValue($value)
    {
        $value = parent::processSaveValue($value);

        if (!$this->isSupported)
            return $value;

        $fieldName = implode('.', name_to_array($this->fieldName));
        $localeData = array_get(post('TRLTranslate'), $fieldName);

        $result = [];
        foreach ($value as $index => $data) {
            $result[$index] = array_merge($data, array_get($localeData, $index, []));
        }

        return $result;
    }

    public function getLocaleSaveValue($value, $fieldName)
    {
//        $fieldName = $this->valueFrom ?: $this->fieldName;
        $localeData = $this->getLocaleSaveData($fieldName);

        $studKey = Str::studly(implode(' ', name_to_array($fieldName)));
        $mutateMethod = 'set'.$studKey.'AttributeTranslatedValue';

        foreach ($localeData as $locale => $_value) {
            if ($this->model->methodExists($mutateMethod)) {
                $this->model->$mutateMethod($_value, $locale);
            }
            elseif ($this->model->methodExists('setAttributeTranslatedValue')) {
                $this->model->setAttributeTranslatedValue($fieldName, $_value, $locale);
            }
        }

        return $value;
    }

    public function getLocaleSaveData($fieldName = null)
    {
        $values = [];
        $data = post('TRLTranslate');

        if (!is_array($data))
            return $values;

        if (is_null($fieldName))
            $fieldName = implode('.', name_to_array($this->fieldName));

        foreach ($data as $locale => $_data) {
            $value = array_get($_data, $fieldName);
            $values[$locale] = $value;
        }

        return $values;
    }
}
