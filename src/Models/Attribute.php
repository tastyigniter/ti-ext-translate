<?php

declare(strict_types=1);

namespace Igniter\Translate\Models;

use Igniter\Flame\Database\Model;

/**
 * Attribute Model
 *
 * @property int $id
 * @property string $locale
 * @property string|null $translatable_id
 * @property string|null $translatable_type
 * @property string|null $attribute
 * @mixin Model
 */
class Attribute extends Model
{
    public $table = 'igniter_translate_attributes';

    protected $guarded = [];

    public $relation = [
        'morphTo' => [
            'translatable' => [],
        ],
    ];
}
