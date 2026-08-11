<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // atom | molecule | organism | section | template
            $table->string('nom');
            $table->foreignId('variant_of_id')->nullable()->constrained('component_definitions')->nullOnDelete();
            $table->string('variant_nom')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon | publie
            // Pas de contrainte FK ici (dépendance circulaire avec component_versions,
            // créée juste après) : intégrité gérée côté application.
            $table->unsignedBigInteger('version_courante_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_definitions');
    }
};
