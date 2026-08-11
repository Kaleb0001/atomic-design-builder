<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_definition_id')->constrained()->cascadeOnDelete();
            $table->longText('code_html')->nullable();
            $table->longText('code_css')->nullable();
            $table->longText('code_js')->nullable();
            $table->foreignId('auteur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_versions');
    }
};
