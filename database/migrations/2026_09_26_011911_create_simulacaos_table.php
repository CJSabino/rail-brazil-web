<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulacoes', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento: De quem é a simulação
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Dados da Rota
            $table->string('origem');
            $table->string('destino');
            
            // Dados da Carga
            $table->string('mercadoria');
            $table->integer('toneladas');
            
            // Resultados
            $table->decimal('distancia_km', 8, 2);
            $table->decimal('custo_frete', 15, 2);
            $table->decimal('economia_co2', 15, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulacoes');
    }
};