<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ManukMinasyan\FilamentCustomField\Models\CustomField;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('custom-fields.table_names.custom_field_options'), function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(CustomField::class);
            $table->string('name')->nullable();
            $table->unsignedBigInteger('sort_order')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('custom-fields.table_names.custom_field_options'));
    }
};
