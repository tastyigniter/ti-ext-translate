<?php

namespace Igniter\Translate\Models;

use Igniter\Flame\Database\Model;

/**
 * Attribute Model
 */
class Attribute extends Model
{
    public $table = 'igniter_translate_attributes';

    protected $guarded = [];

    public $morphTo = [
        'translatable' => [],
    ];
}
