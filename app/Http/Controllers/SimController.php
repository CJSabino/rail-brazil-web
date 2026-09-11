<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\TarifaRegulada;

class SimController extends Controller
{
    // TELAS
    public function index()
    {
        return view('home');
    }

    public function simulador()
    {
        return view('simulador');
    }

    public function metodologia()
    {
        return view('metodologia');
    }

    // --- MÉTODOS DE API ---

    public function calcularFrete(Request $request)
    {
        $distanciaTotal = $request->input('distanciaKm');
        $concessionaria = $request->input('concessionaria');
        $mercadoria = $request->input('mercadoria');
        $toneladas = $request->input('toneladas');

        $faixas = TarifaRegulada::where('concessionaria', $concessionaria)
            ->where('mercadoria', $mercadoria)
            ->where('tipo_tarifa', 'TETO_ANTT')
            ->orderBy('km_inicial', 'ASC')
            ->get();

        if ($faixas->isEmpty()) {
            return response()->json(['erro' => 'Tarifa não encontrada para esta rota/carga.']);
        }

        $custoVariavelTotal = 0;
        $parcelaFixa = $faixas->first()->parcela_fixa;

        if ($concessionaria === 'RMC') {
            $distanciaTotal = ceil($distanciaTotal / 20) * 20;
        }

        foreach ($faixas as $faixa) {
            $inicio = $faixa->km_inicial;
            $fim = $faixa->km_final;

            if ($distanciaTotal >= $inicio || $inicio == 0) {
                $limiteInferior = ($inicio == 0) ? 0 : $inicio - 1;
                $kmNestaFaixa = min($distanciaTotal, $fim) - $limiteInferior;
                $custoVariavelTotal += ($kmNestaFaixa * $faixa->parcela_variavel);
            }
        }

        $tarifaUnitaria = $parcelaFixa + $custoVariavelTotal;
        $custoFreteTotal = $tarifaUnitaria * $toneladas;

        $economia = $custoFreteTotal * 0.38;
        $consumoLitros = (($distanciaTotal * $toneladas) / 1000) * 3.33;

        return response()->json([
            'sucesso' => true,
            'custoFrete' => $custoFreteTotal,
            'economia' => $economia,
            'litros' => $consumoLitros
        ]);
    }
    // INTEGRAÇÕES COM SUPABASE 

    private $supaKey;
    private $supaUrl;

    public function __construct()
    {
        $this->supaKey = env('SUPABASE_KEY');
        $this->supaUrl = env('SUPABASE_URL');
    }

    public function getTerminais()
    {
        $response = Http::withHeaders([
            'apikey' => $this->supaKey,
            'Authorization' => 'Bearer ' . $this->supaKey
        ])->post($this->supaUrl . 'rpc/get_terminais_geojson');

        return response($response->body())->header('Content-Type', 'application/json');
    }

    public function getRota(Request $request)
    {
        $lat1 = $request->input('lat1');
        $lng1 = $request->input('lng1');
        $lat2 = $request->input('lat2');
        $lng2 = $request->input('lng2');

        $headers = [
            'apikey' => $this->supaKey,
            'Authorization' => 'Bearer ' . $this->supaKey
        ];

        //Pega nó de origem
        $nodeStart = Http::withHeaders($headers)
            ->post($this->supaUrl . 'rpc/get_nearest_node', ['lat' => (float) $lat1, 'lng' => (float) $lng1])
            ->json();

        //Pega nó de destino
        $nodeEnd = Http::withHeaders($headers)
            ->post($this->supaUrl . 'rpc/get_nearest_node', ['lat' => (float) $lat2, 'lng' => (float) $lng2])
            ->json();

        if (!is_numeric($nodeStart) || !is_numeric($nodeEnd)) {
            return response()->json(["error" => "Não foi possível encontrar um trilho próximo."], 400);
        }

        //Calcula a Rota
        $rota = Http::withHeaders($headers)
            ->post($this->supaUrl . 'rpc/get_rail_route', [
                'source_id' => (int) $nodeStart,
                'target_id' => (int) $nodeEnd
            ])->json();

        return response()->json($rota);
    }

    public function getMalha()
    {
        $dados = Http::withHeaders([
            'apikey' => $this->supaKey,
            'Authorization' => 'Bearer ' . $this->supaKey
        ])->get($this->supaUrl . "malha_ferroviaria?select=linha,concessionaria,bitola,sentido,uf,geom")
            ->json();

        $featuresArray = [];

        //Verifica se o Supabase devolveu um array válido antes de montar o GeoJSON
        if (is_array($dados)) {
            foreach ($dados as $item) {
                if (isset($item['geom'])) {
                    $featuresArray[] = [
                        "type" => "Feature",
                        "properties" => [
                            "linha" => $item['linha'] ?? 'Sem Nome',
                            "concessionaria" => trim($item['concessionaria'] ?? 'N/A'),
                            "bitola" => $item['bitola'] ?? 'N/A',
                            "sentido" => $item['sentido'] ?? 'N/A',
                            "uf" => $item['uf'] ?? ''
                        ],
                        "geometry" => $item['geom']
                    ];
                }
            }
        }

        return response()->json([
            "type" => "FeatureCollection",
            "features" => $featuresArray
        ]);
    }
}