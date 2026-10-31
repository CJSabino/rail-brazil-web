<?php

namespace App\Http\Controllers;

use App\Models\TarifaFixa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarifaFixaController extends Controller
{
    // Lista todas as cargas com o valor que vale para o usuário logado
    public function index()
    {
        $userId = auth()->id();

        $padrao = TarifaFixa::whereNull('user_id')->pluck('valor_por_tonelada', 'mercadoria');
        $propria = TarifaFixa::where('user_id', $userId)->pluck('valor_por_tonelada', 'mercadoria');

        $lista = collect(TarifaFixa::CARGAS)->map(function ($carga) use ($padrao, $propria) {
            $temPadrao = isset($padrao[$carga]);
            $temPropria = isset($propria[$carga]);

            return [
                'mercadoria'    => $carga,
                'padrao'        => $temPadrao ? (float) $padrao[$carga] : null,
                'valor'         => $temPropria ? (float) $propria[$carga] : ($temPadrao ? (float) $padrao[$carga] : null),
                'personalizada' => $temPropria,
            ];
        });

        return response()->json($lista);
    }

    // Cria ou atualiza a tarifa personalizada do usuário logado
    public function salvar(Request $request)
    {
        $dados = $request->validate([
            'mercadoria' => ['required', Rule::in(TarifaFixa::CARGAS)],
            'valor'      => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        TarifaFixa::updateOrCreate(
            ['user_id' => auth()->id(), 'mercadoria' => $dados['mercadoria']],
            ['valor_por_tonelada' => $dados['valor']]
        );

        return response()->json(['status' => 'sucesso']);
    }

    // Remove a personalização do usuário e volta a valer a tarifa padrão
    public function restaurar(Request $request)
    {
        $dados = $request->validate([
            'mercadoria' => ['required', Rule::in(TarifaFixa::CARGAS)],
        ]);

        TarifaFixa::where('user_id', auth()->id())
            ->where('mercadoria', $dados['mercadoria'])
            ->delete();

        return response()->json(['status' => 'sucesso']);
    }
}
