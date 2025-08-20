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
        Schema::table('users', function (Blueprint $table) {
            // Profile fields
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->text('headline')->nullable();
            $table->text('about')->nullable();

            // Links
            $table->string('twitter_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('facebook_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             $table->dropColumn([
                'first_name',
                'last_name',
                'headline',
                'about',
                'twitter_link',
                'linkedin_link',
                'youtube_link',
                'facebook_link',
            ]);
        });
    }
};
