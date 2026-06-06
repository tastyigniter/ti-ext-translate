<?php

declare(strict_types=1);

namespace Igniter\Translate\Tests;

use Igniter\System\Models\Language;
use Illuminate\Http\Request;
use Mockery;
use SamPoyigi\Testbench\TestCase;

uses(TestCase::class)->in(__DIR__);

function mockRequest(array $data)
{
    $mockRequest = Mockery::mock(Request::class);
    $mockRequest->shouldReceive('post')->andReturn($data);
    $mockRequest->shouldReceive('setUserResolver')->andReturnNull();
    app()->instance('request', $mockRequest);

    return $mockRequest;
}

function createSupportedLanguages(): void
{
    Language::$localesCache = [];
    Language::$activeLanguage = null;
    Language::$supportedLocalesCache = null;
    Language::clearDefaultModels();

    Language::factory()->createQuietly([
        'code' => 'fr', 'name' => 'French', 'status' => 1, 'is_default' => 0,
    ]);
}
