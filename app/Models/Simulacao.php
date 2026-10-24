<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Simulacao extends Model
{
    protected $table = 'simulacoes';

    protected $fillable = [
        'user_id', 
        'origem', 
        'destino', 
        'mercadoria', 
        'toneladas', 
        'distancia_km', 
        'custo_frete', 
        'economia_co2'
    ];

    // Relacionamento: Uma simulação pertence a um Utilizador (User)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}