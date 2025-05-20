<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCocinaVerificaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cocina_verificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained('cocina_logs')->onDelete('cascade');
            $table->foreignId('verificador_id')->constrained('users');
            $table->foreignId('revisor_id')->constrained('users');
            $table->time('hora_verificacion');
            $table->time('hora_revision_registros');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cocina_verificaciones');
    }
}
