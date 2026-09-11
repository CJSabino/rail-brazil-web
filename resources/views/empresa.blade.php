@extends('layouts.app')

@section('titulo', 'Metodologia')

@section('css')
<style>
    .page-title { color: #1e3a8a; font-size: 1.5rem; font-weight: 700; text-align: center; text-transform: uppercase; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; }
    .section-header { background-color: #f1f5f9; color: #334155; padding: 12px 20px; font-weight: 600; font-size: 1.1rem; margin-top: 2rem; margin-bottom: 1rem; }
    .content-block { padding: 0 20px; }
    .calc-box { background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; margin: 16px 0; border-radius: 0 8px 8px 0; }
    .formula { font-weight: 700; color: #0f172a; font-size: 1.05rem; display: block; margin-bottom: 8px; }
    .source-text { font-size: 0.85rem; color: #64748b; margin-top: 8px; display: flex; align-items: flex-start; gap: 6px; }
    ul.styled-list { list-style-type: disc; padding-left: 2rem; margin-bottom: 1rem; space-y: 0.5rem; }
</style>
@endsection

@section('conteudo')
<div class="max-w-[1000px] mx-auto px-4 w-full py-8">
    <h1 class="page-title">Metodologia e Regras de Negócio do Simulador</h1>

    <div class="section-header">Parâmetros Físicos e Volume Operacional</div>
    <div class="content-block">
        <p class="mb-4">Para dimensionar a simulação logística, o sistema baseia-se nas duas métricas oficiais de aferição de transporte terrestre de cargas do país:</p>
        <ul class="styled-list">
            <li><strong>TU (Tonelada Útil):</strong> Refere-se ao peso real da carga movimentada.</li>
            <li><strong>TKU (Tonelada-Quilômetro Útil):</strong> É a métrica padrão de esforço ferroviário.</li>
        </ul>
        <div class="calc-box">
            <span class="formula">Fórmula: TKU = Tonelada Útil (TU) × Distância da Rota (km)</span>
        </div>
        <p class="source-text"><strong>Fonte:</strong> Documento "16 - TERMINOLOGIA BÁSICA", publicado pela Agência Nacional de Transportes Terrestres (ANTT).</p>
    </div>
    <div class="section-header">
            Eficiência Energética e Pegada de Carbono (ESG)
        </div>
        <div class="content-block">
            <p class="mb-4">O modal ferroviário possui alta eficiência energética e menor emissão de gases poluentes quando comparado ao modal rodoviário de longo curso. O simulador projeta o consumo e as emissões poupadas com base em médias nacionais.</p>
            
            <div class="calc-box">
                <span class="formula">Cálculo de Diesel (L) = (TKU ÷ 1.000) × 3,33 Litros</span>
                <span class="formula mt-3">Economia de CO₂ (kg) = TKU × 0,075 kg</span>
                <p class="text-sm text-slate-600 mt-2">A economia de carbono assume que um caminhão emite, em média, 75 gramas a mais de CO₂ por TKU do que um trem de carga.</p>
            </div>

            <p class="source-text">
                
                <strong>Fonte:</strong> Relatórios de desempenho logístico da Associação Nacional dos Transportadores Ferroviários (ANTF, 2023) e Sistema de Estimativas de Emissões de Gases de Efeito Estufa (SEEG).
            </p>
        </div>

        <div class="section-header">
            Formação de Custos e Tarifa Teto
        </div>
        <div class="content-block">
            <p class="mb-4">A simulação financeira utiliza o modelo de <strong>Tarifa Teto Escalonada</strong>, fundamentado nas resoluções vigentes da ANTT (Decisões SUFER de 2025 e 2026). Que aplica taxas específicas para cada concessionária e mercadoria de forma progressiva.</p>
            
            <div class="calc-box bg-slate-50 p-6 rounded-2xl border border-slate-200">
        <span class="formula">
            Tmax = Pfix + Σ (Dist_faixa × Pvar_faixa)
        </span>
        <ul class="text-sm mt-3 space-y-2 text-slate-600">
            <li><strong>Lógica de Cálculo:</strong></li>
            <li><span class="font-bold">Parcela Fixa:</span> Valor homologado por tonelada, invariável pela distância.</li>
            <li><span class="font-bold">Custo Variável Escalonado:</span> A taxa por quilômetro decresce conforme a carga atinge faixas superiores (0-400km, 401-800km, 801-1600km e >1600km).</li>
            <li><span class="font-bold">Categorias Reguladas:</span> O sistema consulta automaticamente a tabela para Soja, Açúcar, Combustíveis, Minérios, Celulose e Contêineres.</li>
        </ul>
    </div>

    <p class="source-text">
        <strong>Fonte:</strong> Agência Nacional de Transportes Terrestres (ANTT) - Decisões SUFER nº 23/2026 e nº 02/2026.
    </p>
</div>

        <div class="section-header">
            Gargalos de Malha: O Desafio das Bitolas
        </div>
        <div class="content-block">
            <p class="mb-4">O sistema tem em conta restrições físicas da malha real. As ferrovias brasileiras operam predominantemente com bitola larga (1,60m) e bitola métrica (1,00m). O cruzamento entre malhas incompátiveis exige o <strong>Transbordo</strong> (troca de trem).</p>
            
            <div class="calc-box">
                <span class="formula">Lógica de Transbordo:</span>
                <p class="text-sm mt-2">
					Se entre os dois pontos tiver uma troca entre Bitola Larga ou Bitola Métrica será adicionado 24 horas ao Tempo em Marcha para simular a troca de Locomotiva
                    e tambem um custo fixo adicional de R$ 15,00 por TU. Isso é para simular o transtorno feito e reclamado em artigos sobre esse grave problema.
                </p>
            </div>

            <p class="source-text mb-8">
                
                <strong>Fonte:</strong> "Dimensionamento do Potencial de Investimentos do Setor Ferroviário" (Leandro Badini Villar e Dalmo dos Santos Marchetti - Área de Infra-Estrutura e Energia do BNDES).
            </p>
        </div>
@endsection