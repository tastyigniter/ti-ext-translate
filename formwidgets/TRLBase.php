<?php

namespace Igniter\Translate\FormWidgets;

use Igniter\Flame\Support\Str;
use System\Models\Languages_model;

trait TRLBase
{
    /**
     * @var string
     */
    protected $activeLocale;

    /**
     * @var bool
     */
    public $isSupported;

    protected $parentPartialPath;

    protected $parentAssetPath;

    public function initLocale()
    {
        $this->activeLocale = Languages_model::getActiveLocale();
        $this->isSupported = Languages_model::supportsLocale();
    }

    public function prepareLocaleVars()
    {
        $this->vars['activeLocale'] = $this->activeLocale;
        $this->vars['locales'] = Languages_model::listSupported();
        $this->vars['field'] = $this->makeRenderFormField();
    }

    public function loadLocaleAssets()
    {
        $this->addJs('$/igniter/translate/assets/js/translatable.js', 'translatable-js');
        $this->addCss('$/igniter/translate/assets/css/translatable.css', 'translatable-css');
    }

    public function renderFallbackField()
    {
        return $this->makeTRLPartial('trlbase/fallback_field');
    }

    public function makeTRLPartial($partial, $params = [])
    {
        $oldViewPath = $this->viewPath;
        $this->viewPath = strtolower(str_replace('\\', '/', __TRAIT__));
        $result = $this->makePartial($partial, $params);
        $this->viewPath = $oldViewPath;

        return $result;
    }

    public function getLocaleValue($locale)
    {
        $key = $this->valueFrom ?: $this->fieldName;

        $studKey = Str::studly(implode(' ', name_to_array($key)));
        $mutateMethod = 'get'.$studKey.'AttributeTranslatedValue';

        if ($this->model->methodExists($mutateMethod)) {
            $value = $this->model->$mutateMethod($locale);
        }
        elseif ($this->activeLocale->code != $locale && $this->model->methodExists('getAttributeTranslatedValue')) {
            $value = $this->model->translatableNoFallbackLocale()->getAttributeTranslatedValue($key, $locale);
        }
        else {
            $value = $this->formField->value;
        }

        return $value;
    }

    public function getLocaleSaveValue($value)
    {
        $localeData = $this->getLocaleSaveData();
        $key = $this->valueFrom ?: $this->fieldName;

        $studKey = Str::studly(implode(' ', name_to_array($key)));
        $mutateMethod = 'set'.$studKey.'AttributeTranslatedValue';

        foreach ($localeData as $locale => $_value) {
            if ($this->model->methodExists($mutateMethod)) {
                $this->model->$mutateMethod($_value, $locale);
            }
            elseif ($this->model->methodExists('setAttributeTranslatedValue')) {
                $this->model->setAttributeTranslatedValue($key, $_value, $locale);
            }
        }

        return array_get($localeData, $this->activeLocale->code, $value);
    }

    public function getLocaleSaveData()
    {
        $values = [];
        $data = post('TRLTranslate');

        if (!is_array($data))
            return $values;

        $fieldName = implode('.', name_to_array($this->fieldName));

        return array_get($data, $fieldName, []);
    }

    public function getFallbackType()
    {
        return defined('static::FALLBACK_TYPE') ? static::FALLBACK_TYPE : 'text';
    }

    protected function makeRenderFormField($fieldType = null)
    {
        if ($this->isSupported)
            return $this->formField;

        $field = clone $this->formField;
        $field->type = $this->getFallbackType();

        return $field;
    }

    protected function maskAsParent($callback)
    {
        $originalAssetPath = $this->assetPath;
        $originalPartialPath = $this->partialPath;
        $this->assetPath = array_merge($this->parentAssetPath, $originalAssetPath);
        $this->partialPath = array_merge($this->parentPartialPath, $originalPartialPath);

        $result = $callback();

        $this->assetPath = $originalAssetPath;
        $this->partialPath = $originalPartialPath;

        return $result;
    }
}
