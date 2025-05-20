<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCocinaMediciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cocina_mediciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained('cocina_logs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('fase', ['cook_min', 'cook_120', 'chill_120_80', 'chill_80_55', 'chill_le40']);
            $table->time('hora');
            $table->decimal('temperatura', 5, 2);
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
        Schema::dropIfExists('cocina_mediciones');
    }
}
