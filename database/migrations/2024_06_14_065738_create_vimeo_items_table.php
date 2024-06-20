<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vimeo_items', function (Blueprint $table) {
            $table->id();
            $table->string('uri');
            $table->string('name');
            $table->string('item_type')->default('video')->comment('video or folder');
            //$table->string('resource_key');

            //$table->string('manage_link');
            $table->unsignedInteger('videos_total')->default(0);
            $table->unsignedInteger('folders_total')->default(0);
            $table->string('link')->nullable();
            $table->text('privacy')->nullable();
            $table->json('pictures')->nullable();
            $table->json('metadata');
            $table->nestedSet();
            $table->timestamps();
            $table->timestamp('last_accessed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vimeo_items');
    }
};
