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
        Schema::disableForeignKeyConstraints();

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable(); // وصف الدرس
            $table->string('video_url');
            $table->integer('video_duration')->nullable(); // مدة الفيديو بالثواني
            $table->json('materials')->nullable(); // المواد الإضافية (ملفات PDF، روابط، إلخ)
            $table->integer('order')->default(0); // ترتيب الدرس في الكورس
            $table->foreignId('course_id')->references('id')->on('courses')->onDelete("cascade");
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson');
    }
};
