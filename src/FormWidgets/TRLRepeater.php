<?php

declare(strict_types=1);

namespace Igniter\Translate\FormWidgets;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Classes\FormField;
use Override;
use Igniter\Admin\FormWidgets\Repeater;
use Igniter\System\Models\Language;
use Illuminate\Support\Str;

class TRLRepeater extends Repeater
{
    use TRLBase;

    protected string $defaultAlias = 'trlrepeater';

    public function __construct(AdminController $controller, FormField $formField, array $configuration = [])
    {
        $this->parentPartialPath[] = '~/app/admin/formwidgets';
        $this->parentPartialPath[] = '~/app/admin/formwidgets/repeater';
        $this->parentAssetPath[] = '~/app/admin/formwidgets/repeater/assets';

        parent::__construct($controller, $formField, $configuration);
    }

    #[Override]
    public function initialize(): void
    {
        parent::initialize();

        $this->initLocale();
    }

    #[Override]
    public function render(): string
    {
        $parentContent = $this->maskAsParent(fn(): string => parent::render());

        if (!$this->isSupported) {
            return $parentContent;
        }

        $this->vars['repeater'] = $parentContent;

        return $this->makePartial('trlrepeater/trlrepeater');
    }

    #[Override]
    public function prepareVars(): void
    {
        parent::prepareVars();
        $this->prepareLocaleVars();
    }

    #[Override]
    public function loadAssets(): void
    {
        $this->maskAsParent(function(): void {
            parent::loadAssets();
        });

        if (Language::supportsLocale()) {
            $this->loadLocaleAssets();
            $this->addJs('js/trlrepeater.js');
        }
    }

    #[Override]
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

    #[Override]
    protected function processSaveValue(mixed $value): mixed
    {
        $value = parent::processSaveValue($value);

        if (!$this->isSupported) {
            return $value;
        }

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
        $localeData = $this->getLocaleSaveData($fieldName);

        $studKey = Str::studly(implode(' ', name_to_array($fieldName)));
        $mutateMethod = 'set'.$studKey.'AttributeTranslatedValue';

        foreach ($localeData as $locale => $_value) {
            if ($this->model->methodExists($mutateMethod)) {
                $this->model->$mutateMethod($_value, $locale);
            } elseif ($this->model->methodExists('setAttributeTranslatedValue')) {
                $this->model->setAttributeTranslatedValue($fieldName, $_value, $locale);
            }
        }

        return $value;
    }

    public function getLocaleSaveData($fieldName = null)
    {
        $values = [];
        $data = post('TRLTranslate');

        if (!is_array($data)) {
            return $values;
        }

        if (is_null($fieldName)) {
            $fieldName = implode('.', name_to_array($this->fieldName));
        }

        foreach ($data as $locale => $_data) {
            $value = array_get($_data, $fieldName);
            $values[$locale] = $value;
        }

        return $values;
    }
}
