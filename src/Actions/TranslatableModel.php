<?php

declare(strict_types=1);

namespace Igniter\Translate\Actions;

use Igniter\Flame\Database\Model;
use Igniter\Translate\Models\Attribute;
use Override;

class TranslatableModel extends TranslatableAction
{
    public function __construct(protected ?Model $model = null)
    {
        parent::__construct($model);

        $model->relation['morphMany']['translations'] = [
            Attribute::class, 'name' => 'translatable',
        ];
    }

    #[Override]
    protected function storeTranslatableAttributes($locale = null)
    {
        if (!$locale) {
            $locale = $this->translatableActiveLocale;
        }

        if (!$this->model->exists) {
            $this->model->bindEventOnce('model.afterCreate', function() use ($locale): void {
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

    #[Override]
    protected function loadTranslatableAttributes($locale = null): array
    {
        if (!$locale) {
            $locale = $this->translatableActiveLocale;
        }

        if (!$this->model->exists) {
            return $this->translatableAttributes[$locale] = [];
        }

        // Query the attributes table directly. Loading via the translations
        // morphMany relation is unsafe while Relation::noConstraints() is
        // active (e.g. admin Relation form widgets), because parent scoping
        // is disabled and every model can pick up another model's translation.
        /** @var Attribute|null $translation */
        $translation = Attribute::query()
            ->where('locale', $locale)
            ->where('translatable_id', $this->model->getKey())
            ->where('translatable_type', $this->model->getMorphClass())
            ->first();

        $result = $translation ? json_decode((string)$translation->attribute, true) : [];

        return $this->translatableOriginals[$locale] = $this->translatableAttributes[$locale] = is_array($result) ? $result : [];
    }
}
