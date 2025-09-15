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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            //   $table->string('name');
            // Profile fields
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
         //   $table->text('headline')->nullable();
            $table->text('about')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('total_earnings', 10, 2)->default(0);
            // Links
            $table->string('twitter_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('facebook_link')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('image')->nullable();
            $table->enum('role', ["learner", "instructor", "admin"]);
            $table->enum('status', ["Active", "Blocked"]);
            $table->string('nationality')->nullable();
            $table->rememberToken();
            $table->string('stripe_customer_id')->nullable()->unique();
            $table->string('verification_code')->nullable(); // OTP
            $table->boolean('is_verified')->default(false);
            $table->string('provider')->nullable(); // google, facebook
            $table->string('provider_id')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
