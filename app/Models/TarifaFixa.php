<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaFixa extends Model
{
    protected $table = 'tarifas_fixas';

    protected $fillable = ['user_id', 'mercadoria', 'valor_por_tonelada'];

    public const CARGAS = [
        'Soja',
        'Farelo de Soja',
        'Milho',
        'Trigo',
        'Açúcar',
        'Óleo Vegetal',
        'Adubos e Fertilizantes',
        'Ferro Gusa',
        'Minério de Ferro',
        'Produtos Siderúrgicos',
        'Calcário Siderúrgico',
        'Escória',
        'Óleo Diesel',
        'Gasolina',
        'Álcool',
        'Celulose',
        'Cimento Acondicionado',
        'Contêiner Cheio de 20 pés',
        'Veículos',
        'Demais Produtos',
    ];

    /**
     * Valor em R$/t para a mercadoria: a tarifa do usuário, se existir; senão a padrão; senão null.
     */
    public static function valorPara(string $mercadoria, ?int $userId): ?float
    {
        $valor = null;

        if ($userId) {
            $valor = static::where('user_id', $userId)
                ->where('mercadoria', $mercadoria)
                ->value('valor_por_tonelada');
        }

        $valor ??= static::whereNull('user_id')
            ->where('mercadoria', $mercadoria)
            ->value('valor_por_tonelada');

        return $valor === null ? null : (float) $valor;
    }
}
