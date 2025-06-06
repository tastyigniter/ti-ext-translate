<?php

declare(strict_types=1);

namespace Igniter\Translate\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('igniter_translate_attributes', function(Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('locale')->index();
            $table->string('translatable_id')->index()->nullable();
            $table->string('translatable_type')->index()->nullable();
            $table->mediumText('attribute')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('igniter_translate_attributes');
    }
};
