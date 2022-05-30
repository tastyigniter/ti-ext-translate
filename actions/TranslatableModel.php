<?php

namespace Igniter\Translate\Actions;

use Igniter\Translate\Models\Attribute;

class TranslatableModel extends TranslatableAction
{
    public function __construct($model)
    {
        parent::__construct($model);

        $model->relation['morphMany']['translations'] = [
            \Igniter\Translate\Models\Attribute::class, 'name' => 'translatable',
        ];
    }

    protected function storeTranslatableAttributes($locale = null)
    {
        if (!$locale)
            $locale = $this->translatableActiveLocale;

        if (!$this->model->exists) {
            $this->model->bindEventOnce('model.afterCreate', function () use ($locale) {
                $this->storeTranslatableAttributes($locale);
            });

            return;
        }

        $data = json_encode($this->translatableAttributes[$locale], JSON_UNESCAPED_UNICODE);

        Attribute::updateOrCreate([
            'locale' => $locale,
            'translatable_id' => $this->model->getKey(),
            'translatable_type' => $this->model->getMorphClass(),
        ], [
            'attribute' => $data,
        ]);
    }

    protected function loadTranslatableAttributes($locale = null)
    {
        if (!$locale)
            $locale = $this->translatableActiveLocale;

        if (!$this->model->exists)
            return $this->translatableAttributes[$locale] = [];

        $translation = $this->model->translations->first(function ($value, $key) use ($locale) {
            return $value->getAttribute('locale') === $locale;
        });

        $result = $translation ? json_decode($translation->attribute, true) : [];

        return $this->translatableOriginals[$locale] = $this->translatableAttributes[$locale] = $result;
    }
}
