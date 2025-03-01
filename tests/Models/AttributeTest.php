<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests\Models;

use Igniter\Translate\Models\Attribute;

it('configures attribute model correctly', function(): void {
    $model = new Attribute;

    expect($model->table)->toEqual('igniter_translate_attributes')
        ->and($model->getGuarded())->toBe([])
        ->and($model->relation['morphTo']['translatable'])->toBe([]);
});
