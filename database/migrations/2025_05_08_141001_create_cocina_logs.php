<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCocinaLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cocina_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('insumo_id');
            $table->integer('presentacion_id');
            $table->date('fecha');
            $table->enum('ccp_code', ['1B', '1B-1', '2B', '2B-1']);
            $table->text('observaciones')->nullable();

            // Relaciones bien formadas:
            $table->foreign('insumo_id')
                ->references('id')->on('insumos')
                ->onDelete('restrict'); // o 'cascade' según lo que necesites

            $table->foreign('presentacion_id')
                ->references('id')->on('presentaciones')
                ->onDelete('restrict');

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
        Schema::dropIfExists('cocina_logs');
    }
}
