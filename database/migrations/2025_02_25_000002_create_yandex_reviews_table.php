<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yandex_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yandex_setting_id')->constrained()->cascadeOnDelete();
            $table->string('author_name')->nullable();
            $table->string('author_phone')->nullable();
            $table->string('branch_name')->nullable();
            $table->dateTime('review_date')->nullable();
            $table->text('text');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yandex_reviews');
    }
};
