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
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->text('mission');
            $table->text('vision');
            $table->json('values');
            $table->string('name_yt');
            $table->string('url_yt');
            $table->timestamps();
        });
    }

    /**
     * Sql para agregar un registro de about me
     * INSERT INTO about_us (mission, vision, `values`, name_yt, url_yt, created_at, updated_at) 
        VALUES (
            'Nuestra misión es innovar constantemente.',
            'Nuestra visión es liderar el sector tecnológico.',
            '["Innovación", "Compromiso", "Integridad", "Respeto", "Calidad"]',
            'Canal Oficial',
            'https://www.youtube.com/channel/ejemplo',
            NOW(), NOW()
        );

     */

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
