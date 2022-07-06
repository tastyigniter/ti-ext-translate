<?php

namespace Igniter\Translate\Actions;

use Igniter\Flame\Support\Str;
use System\Actions\ModelAction;

/**
 * Translatable Model Action base Class
 *
 * Adapted from rainlab\translate\classes\TranslatableBehavior
 */
abstract class TranslatableAction extends ModelAction
{
    /**
     * @var \Igniter\Flame\Database\Model|self Reference to the extended model.
     */
    protected $model;

    /**
     * @var string Active locale for translations.
     */
    protected $translatableActiveLocale;

    /**
     * @var string Default system locale.
     */
    protected $translatableDefaultLocale;

    /**
     * @var bool Determines if empty translations should be replaced by default values.
     */
    protected $translatableUseFallback = true;

    /**
     * @var array Data store for translated attributes.
     */
    protected $translatableAttributes = [];

    /**
     * @var array Data store for original translated attributes.
     */
    protected $translatableOriginals = [];

    /**
     * @var array Properties that must exist in the model using this action.
     */
    protected $requiredProperties = [];

    /**
     * Constructor
     * @param \Illuminate\Database\Eloquent\Model $model The extended model.
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $this->initTranslatableLocale();

        $this->model->bindEvent('model.beforeGetAttribute', function ($key) {
            if ($this->isTranslatableAttribute($key)) {
                return $this->performGetTranslatableAttribute($key);
            }
        });

        $this->model->bindEvent('model.beforeSetAttribute', function ($key, $value) {
            if ($this->isTranslatableAttribute($key)) {
                return $this->performSetTranslatableAttribute($key, $value);
            }
            elseif (in_array($key, $this->model->getTranslatableAttributes()) && is_array($value)) {
                if (array_key_exists($this->translatableActiveLocale, $value)) {
                    foreach ($value as $locale => $_value) {
                        $this->setAttributeTranslatedValue($key, $_value, $locale);
                    }

                    return array_get($value, $this->translatableActiveLocale, $value);
                }
            }
        });

        $this->model->bindEvent('model.saveInternal', function () {
            $this->syncTranslatableAttributes();
        });
    }

    public function initTranslatableLocale()
    {
        $localization = app('translator.localization');
        $this->translatableActiveLocale = $localization->getLocale();
        $this->translatableDefaultLocale = $localization->getDefaultLocale();
    }

    public function performGetTranslatableAttribute($key)
    {
        $value = $this->getAttributeTranslatedValue($key);

        if ($this->model->hasGetMutator($key)) {
            $method = 'get'.Str::studly($key).'Attribute';
            $value = $this->model->{$method}($value);
        }

        return $value;
    }

    public function performSetTranslatableAttribute($key, $value)
    {
        $value = $this->setAttributeTranslatedValue($key, $value);
        if ($this->model->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';
            $value = $this->model->{$method}($value);
        }

        return $value;
    }

    public function syncTranslatableAttributes()
    {
        $availableLocales = array_keys($this->translatableAttributes);
        foreach ($availableLocales as $locale) {
            if (!$this->isTranslatableDirty(null, $locale)) continue;

            $this->storeTranslatableAttributes($locale);
        }

        if ($this->translatableActiveLocale == $this->translatableDefaultLocale)
            return;

        // Restore translatable values to originals
        $original = $this->model->getOriginal();
        $attributes = $this->model->getAttributes();
        $translatable = $this->model->getTranslatableAttributes();
        $originalValues = array_intersect_key($original, array_flip($translatable));
        $this->model->setRawAttributes(array_merge($attributes, $originalValues));
    }

    public function translatableSetActiveLocale($locale = null)
    {
        $this->translatableActiveLocale = $locale;

        return $this->model;
    }

    public function isTranslatableAttribute($key)
    {
        if ($key === 'translatable' || $this->translatableDefaultLocale == $this->translatableActiveLocale) {
            return false;
        }

        if ($this->model->hasRelation($key))
            return false;

        return in_array($key, $this->model->getTranslatableAttributes());
    }

    public function isTranslatableDirty($attribute = null, $locale = null)
    {
        $dirty = $this->translatableGetDirty($locale);

        if (is_null($attribute))
            return count($dirty) > 0;

        return array_key_exists($attribute, $dirty);
    }

    public function translatableGetDirty($locale = null)
    {
        if (!$locale)
            $locale = $this->translatableActiveLocale;

        if (!array_key_exists($locale, $this->translatableAttributes))
            return [];

        // All dirty
        if (!array_key_exists($locale, $this->translatableOriginals))
            return $this->translatableAttributes[$locale];

        $dirty = [];
        foreach ($this->translatableAttributes[$locale] as $key => $value) {
            if (!array_key_exists($key, $this->translatableOriginals[$locale])) {
                $dirty[$key] = $value;
            }
            elseif ($value != $this->translatableOriginals[$locale][$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Disables translation fallback locale.
     * @return self
     */
    public function translatableNoFallbackLocale()
    {
        $this->translatableUseFallback = false;

        return $this->model;
    }

    public function hasTranslation($key, $locale)
    {
        if ($locale == $this->translatableActiveLocale) {
            $translatableAttributes = $this->model->getAttributes();
        }
        else {
            if (!isset($this->translatableAttributes[$locale]))
                $this->loadTranslatableAttributes($locale);

            $translatableAttributes = $this->translatableAttributes[$locale];
        }

        return (bool)$this->getAttributeFromData($translatableAttributes, $key);
    }

    public function getAttributeTranslatedValue($key, $locale = null)
    {
        if (is_null($locale))
            $locale = $this->translatableActiveLocale;

        $result = '';

        if ($locale == $this->translatableDefaultLocale) {
            $result = $this->getAttributeFromData($this->model->getAttributes(), $key);
        }
        else {
            if (!array_key_exists($locale, $this->translatableAttributes)) {
                $this->loadTranslatableAttributes($locale);
            }

            if ($this->hasTranslation($key, $locale)) {
                $result = $this->getAttributeFromData($this->translatableAttributes[$locale], $key);
            }
            elseif ($this->translatableUseFallback) {
                $result = $this->getAttributeFromData($this->model->getAttributes(), $key);
            }
        }

        return $result;
    }

    public function setAttributeTranslatedValue($key, $value, $locale = null)
    {
        if (is_null($locale))
            $locale = $this->translatableActiveLocale;

        if ($locale == $this->translatableDefaultLocale) {
            $attributes = $this->model->getAttributes();

            return $this->setAttributeFromData($attributes, $key, $value);
        }

        if (!array_key_exists($locale, $this->translatableAttributes))
            $this->loadTranslatableAttributes($locale);

        return $this->setAttributeFromData($this->translatableAttributes[$locale], $key, $value);
    }

    public function getTranslatableAttributes()
    {
        return $this->model->translatable() ?? [];
    }

    public function hasTranslatableAttributes()
    {
        return is_array($this->model->translatable()) &&
            count($this->model->translatable()) > 0;
    }

    protected function getAttributeFromData($data, $attribute)
    {
        return array_get($data, implode('.', name_to_array($attribute)));
    }

    protected function setAttributeFromData(&$data, $attribute, $value)
    {
        array_set($data, implode('.', name_to_array($attribute)), $value);

        return $value;
    }

    /**
     * Saves the translation attributes for the model.
     * @param string $locale
     * @return void
     */
    abstract protected function storeTranslatableAttributes($locale = null);

    /**
     * Loads the translation attributes from the model.
     * @param string $locale
     * @return array
     */
    abstract protected function loadTranslatableAttributes($locale = null);

    public static function extend(callable $callback)
    {
        self::extensionExtendCallback($callback);
    }
}
