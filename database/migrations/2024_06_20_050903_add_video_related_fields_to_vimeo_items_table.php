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
        Schema::table('vimeo_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('metadata');
            $table->string('player_embed_url')->nullable()->after('metadata');
            $table->integer('height')->nullable()->after('metadata');
            $table->integer('width')->nullable()->after('metadata');
            $table->string('transcode_status')->nullable()->after('metadata');
            $table->datetime('release_time')->nullable()->after('metadata');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vimeo_items', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('player_embed_url');
            $table->dropColumn('height');
            $table->dropColumn('width');
            $table->dropColumn('transcode_status');
            $table->dropColumn('release_time');
        });
    }
};
