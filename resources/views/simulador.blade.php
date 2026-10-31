@extends('layouts.app')

@section('titulo', 'Simulador de Logística')

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f7f4;
            color: #0f172a;
        }

        #map-container {
            position: relative;
            height: 85vh;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        #map {
            height: 100%;
            width: 100%;
            z-index: 10;
            cursor: crosshair;
        }

        .timeline-container {
            position: relative;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 15px;
            bottom: 15px;
            width: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .timeline-item {
            position: relative;
            padding-left: 2.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            width: 10px;
            height: 10px;
            background: #f59e0b;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px #fef3c7;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .timeline-item.endpoint::before {
            width: 14px;
            height: 14px;
            left: 9px;
            background: #10b981;
            box-shadow: 0 0 0 3px #d1fae5;
        }

        .loader {
            border-top-color: #f59e0b;
            animation: spinner 1s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }

        .custom-tooltip {
            background: rgba(255, 255, 255, 0.95);
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            font-weight: 600;
            font-size: 13px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(4px);
        }

        .leaflet-tooltip-top:before {
            border-top-color: rgba(255, 255, 255, 0.95);
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
@endsection

@section('conteudo')
    <div class="max-w-[1600px] mx-auto px-4 mt-6 mb-12">
        <div class="mb-4">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Painel de Simulação</h1>
            <p class="text-sm text-slate-500">Selecione origem e destino para calcular a rota, tempo e custos operacionais.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- MAPA COM ELEMENTOS FLUTUANTES -->
            <div class="col-span-1 lg:col-span-8">
                <div id="map-container">
                    <!-- Loader -->
                    <div id="map-loader"
                        class="hidden absolute inset-0 bg-white/70 z-[2000] flex flex-col items-center justify-center backdrop-blur-sm transition-opacity duration-300">
                        <div class="loader rounded-full border-4 border-t-4 border-slate-200 h-16 w-16 mb-4 shadow-lg">
                        </div>
                        <h2 class="text-slate-900 text-lg font-bold">Processando Rota...</h2>
                    </div>

                    <!-- Div do Leaflet -->
                    <div id="map" class="bg-[#e5e7eb]"></div>

                    <!-- Overlay Top (Dentro do container, perfeitamente alinhado e clicável) -->
                    <div
                        class="absolute top-0 left-0 right-0 z-[1000] p-4 flex justify-between items-start pointer-events-none">

                        <!-- Botão Toggle da Malha -->
                        <button onclick="toggleMalhaOficial()"
                            class="pointer-events-auto inline-flex items-center gap-3 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors cursor-pointer group">
                            <div class="flex items-center gap-2">
                                <div id="ui-toggle-luz" class="w-2 h-2 rounded-full bg-emerald-500 transition-colors"></div>
                                <span id="ui-toggle-texto"
                                    class="font-mono text-[10px] uppercase text-slate-700 font-bold tracking-wider group-hover:text-slate-900">Malha
                                    Nacional Ativa</span>
                            </div>
                        </button>

                        <!-- Botão Toggle Interrompidas -->
                        <button onclick="toggleInterrompidasOficial()"
                            class="pointer-events-auto inline-flex items-center gap-3 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors cursor-pointer group">
                            <div class="flex items-center gap-2">
                                <!-- Luz começa cinzenta (bg-slate-300) -->
                                <div id="ui-toggle-luz-interrompidas"
                                    class="w-2 h-2 rounded-full bg-slate-300 transition-colors"></div>
                                <span
                                    class="font-mono text-[10px] uppercase text-slate-700 font-bold tracking-wider group-hover:text-slate-900">
                                    Incluir Interrompidas
                                </span>
                            </div>
                        </button>

                        <!-- Botão Importar GeoJSON -->
                        <div class="pointer-events-auto">
                            <input type="file" id="input-geojson" accept=".geojson,application/geo+json,.json"
                                class="hidden" onchange="carregarMalhaPersonalizada(event)">
                            <label for="input-geojson"
                                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-xs font-bold shadow-md hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar Malha
                            </label>
                        </div>
                    </div>

                    <!-- Overlay Bottom Center (Instrução Dinâmica) -->
                    <div id="instrucao-simulador"
                        class="absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-white border border-slate-200 shadow-md transition-all duration-300 text-center">
                        <span id="texto-instrucao" class="text-xs text-slate-600 font-medium">
                            Clique em um terminal de <strong class="text-slate-900">ORIGEM</strong> para iniciar
                        </span>
                    </div>

                    <!-- Overlay Bottom Right -->
                    <div
                        class="absolute bottom-8 right-6 z-[1000] bg-white/95 backdrop-blur-sm p-4 rounded-xl shadow-lg border border-slate-200">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Concessionárias</h3>
                        <ul class="flex flex-col gap-2">
                            <!-- Grupo Rumo -->
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-1 rounded-full bg-[#f59e0b]"></span>
                                <span class="text-xs font-medium text-slate-700">Rumo (Paulista)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-1 rounded-full bg-[#d97706]"></span>
                                <span class="text-xs font-medium text-slate-700">Rumo (Sul/Norte/Oeste)</span>
                            </li>
                            <!-- Grupo Vale -->
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-1 rounded-full bg-[#dc2626]"></span>
                                <span class="text-xs font-medium text-slate-700">EFC / EFVM (Vale)</span>
                            </li>
                            <!-- Grupo VLI -->
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-1 rounded-full bg-[#8b5cf6]"></span>
                                <span class="text-xs font-medium text-slate-700">FCA / FNS (VLI)</span>
                            </li>
                            <!-- Independentes -->
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-1 rounded-full bg-[#10b981]"></span>
                                <span class="text-xs font-medium text-slate-700">MRS Logística</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-4 h-1 rounded-full bg-[#2563eb]"></span>
                                <span class="text-xs font-medium text-slate-700">FTL (Transnordestina)</span>
                            </li>
                            <li class="flex items-center gap-2 border-t border-slate-100 pt-1.5 mt-1">
                                <span class="w-4 h-0.5 border-b-2 border-dashed border-red-600"></span>
                                <span class="text-xs font-semibold text-red-600">Trecho Interrompido</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- =============================PAINEL LATERAL============================= -->

            <div class="col-span-1 lg:col-span-4 flex flex-col gap-4">

                <!-- SELEÇÃO DE ROTA -->
                <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-sm">

                    <!-- Origem -->
                    <div class="mb-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Origem</p>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-semibold text-slate-800 truncate"
                            id="ui-origem-nome">
                            Aguardando seleção no mapa...
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center min-h-[18px]" id="ui-origem-cargas"></div>
                    </div>

                    <!-- Destino -->
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Destino</p>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-semibold text-slate-800 truncate"
                            id="ui-destino-nome">
                            Aguardando seleção no mapa...
                        </div>
                        <div class="mt-1.5 flex flex-wrap items-center min-h-[18px]" id="ui-destino-cargas"></div>
                    </div>
                </div>

                <!-- Calculadora -->
                <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm relative overflow-hidden">
                    <h3
                        class="text-xs font-mono text-amber-600 tracking-widest uppercase mb-4 border-b border-slate-100 pb-2">
                        Parâmetros da Carga</h3>

                    <div class="mb-4">
                        <label class="text-slate-500 font-semibold text-xs mb-1.5 block">Mercadoria</label>
                        <select id="input-tipo-carga" onchange="seRotaProntaRecalcular()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-slate-800 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none cursor-pointer transition-all">
                            <option value="" disabled selected>Selecione o produto...</option>

                            <optgroup label="Agrícolas e Alimentícios">
                                <option value="Soja">Soja em Grão</option>
                                <option value="Farelo de Soja">Farelo de Soja</option>
                                <option value="Milho">Milho</option>
                                <option value="Trigo">Trigo</option>
                                <option value="Açúcar">Açúcar</option>
                                <option value="Óleo Vegetal">Óleo Vegetal</option>
                                <option value="Adubos e Fertilizantes">Adubos e Fertilizantes</option>
                            </optgroup>

                            <optgroup label="Minérios e Siderurgia">
                                <option value="Ferro Gusa">Ferro Gusa</option>
                                <option value="Minério de Ferro">Minério de Ferro</option>
                                <option value="Produtos Siderúrgicos">Produtos Siderúrgicos (Bobinas/Aço)</option>
                                <option value="Calcário Siderúrgico">Calcário Siderúrgico</option>
                                <option value="Escória">Escória de Alto Forno</option>
                            </optgroup>

                            <optgroup label="Combustíveis">
                                <option value="Óleo Diesel">Óleo Diesel</option>
                                <option value="Gasolina">Gasolina</option>
                                <option value="Álcool">Álcool / Etanol</option>
                            </optgroup>

                            <optgroup label="Outros / Carga Geral">
                                <option value="Celulose">Celulose</option>
                                <option value="Cimento Acondicionado">Cimento Acondicionado</option>
                                <option value="Contêiner Cheio de 20 pés">Contêiner Cheio (20 pés)</option>
                                <option value="Veículos">Veículos</option>
                                <option value="Demais Produtos">Carga Geral (Demais Produtos)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="text-slate-500 font-semibold text-xs mb-1.5 block">Tipo de tarifa</label>
                        <div class="grid grid-cols-2 gap-1 p-1 bg-slate-100 rounded-lg">
                            <button type="button" id="btn-modo-regulada" onclick="definirModoTarifa('regulada')"
                                class="px-3 py-1.5 rounded-md text-xs font-bold bg-white text-slate-900 shadow-sm">Regulada ANTT</button>
                            <button type="button" id="btn-modo-fixa" onclick="definirModoTarifa('fixa')"
                                class="px-3 py-1.5 rounded-md text-xs font-bold text-slate-500 hover:text-slate-700">Tarifa fixa</button>
                        </div>
                        <button type="button" id="btn-editar-tarifas" onclick="abrirModalTarifas()"
                            class="hidden mt-2 text-xs font-semibold text-amber-600 hover:text-amber-700 underline">Editar minhas tarifas por carga</button>
                    </div>

                    <div class="mb-6">
                        <label class="text-slate-500 font-semibold text-xs mb-1.5 block">Volume (Toneladas)</label>
                        <div
                            class="flex items-center bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 focus-within:ring-2 focus-within:ring-amber-500 transition-all">
                            <input type="number" id="input-carga" value="500"
                                class="bg-transparent w-full text-xl font-bold text-slate-800 outline-none border-none focus:ring-0 p-0"
                                onchange="seRotaProntaRecalcular()">
                            <span class="text-slate-400 font-bold ml-2 text-sm">TU</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                        <div>
                            <p class="text-slate-400 font-semibold text-[10px] uppercase tracking-wider">Distância</p>
                            <p class="text-xl font-black text-slate-800"><span id="res-km">0.0</span> <span
                                    class="text-xs font-medium text-slate-500">km</span></p>
                        </div>
                        <div>
                            <p class="text-slate-400 font-semibold text-[10px] uppercase tracking-wider">Tempo Estimado</p>
                            <p class="text-base font-bold text-slate-800" id="res-tempo">--</p>
                        </div>
                    </div>
                </div>

                <!-- Manifesto -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex-1 flex flex-col min-h-[300px] max-h-[380px]">
                    <h3
                        class="text-xs font-mono text-amber-600 tracking-widest uppercase mb-4 border-b border-slate-100 pb-2">
                        Manifesto de Percurso
                    </h3>

                    <div id="alerta-transbordo"
                        class="hidden mb-4 p-3 bg-red-50 border border-red-100 rounded-lg flex items-start gap-3">
                        <span class="text-lg leading-none">⚠️</span>
                        <p class="text-xs text-red-700 leading-relaxed">Transbordo detectado: <b id="num-transbordos"></b>
                            quebra(s) de bitola. Custos extras aplicados.</p>
                    </div>

                    <div id="empty-state" class="flex-1 flex flex-col items-center justify-center text-center py-6">
                        <div
                            class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 mb-3 text-slate-300">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-slate-400 px-4">Aguardando seleção de rota no mapa.</p>
                    </div>

                    <div class="overflow-y-auto flex-1 pr-2 timeline-container hidden custom-scrollbar"
                        id="timeline-wrapper">
                        <ul id="lista-percurso" class="space-y-3 py-1"></ul>
                    </div>

                    <button onclick="salvarSimulacao()" id="btn-salvar"
                        class="mt-4 w-full py-3 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition-colors hidden shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                            </path>
                        </svg>
                        Salvar no Dashboard
                    </button>

                    <button onclick="resetarSimulador()" id="btn-reset"
                        class="mt-2 w-full py-3 bg-slate-900 text-white text-sm font-bold rounded-lg hover:bg-slate-800 transition-colors hidden shadow-sm">
                        Nova Simulação
                    </button>

                </div>
            </div>
        </div>

        <!-- DASHBOARD DE RESULTADOS -->
        <div id="dashboard-custos"
            class="grid grid-cols-1 md:grid-cols-4 gap-4 opacity-40 grayscale transition-all duration-700 pointer-events-none mt-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Consumo Combustível</p>
                <p class="text-2xl font-black text-slate-800 mt-2" id="res-litros">0 L</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tarifa Teto ANTT</p>
                <p class="text-2xl font-black text-amber-600 mt-2" id="res-frete">R$ 0,00</p>
            </div>

            <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800 shadow-md flex flex-col justify-between">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Economia vs Rodovia</p>
                <p class="text-2xl font-black text-emerald-400 mt-2" id="res-economia">R$ 0,00</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sustentabilidade (ESG)</p>
                <p class="text-xl font-black text-slate-800 mt-2">
                    <span class="text-emerald-500 font-bold">↓</span> <span id="res-co2">0</span> <span
                        class="text-xs font-medium text-slate-500">kg CO₂</span>
                </p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Modal: tarifas fixas por carga -->
    <div id="modal-tarifas" class="hidden fixed inset-0 z-[2000] items-center justify-center bg-slate-900/50 p-4"
        onclick="if (event.target === this) fecharModalTarifas()">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-800">Minhas tarifas fixas</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Valor em R$ por tonelada, igual para todas as concessionárias. As alterações valem só para a sua conta.</p>
                </div>
                <button type="button" onclick="fecharModalTarifas()" class="text-slate-400 hover:text-slate-700 text-lg leading-none" aria-label="Fechar">&times;</button>
            </div>
            <div id="lista-tarifas" class="overflow-y-auto px-6 py-4 space-y-2"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>

        // --- LÓGICA DO MAPA E API --
        const map = L.map('map', { zoomControl: false }).setView([-15.78, -47.92], 4);
        L.control.zoom({ position: 'bottomleft' }).addTo(map);

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Esri',
            maxZoom: 16
        }).addTo(map);

        const state = { pontos: [], nomes: [], tipoOrigem: null, camadaRota: null, camadaTerminais: null, camadaMalha: null, malhaVisivel: true, isProcessando: false, ultimoData: null };

        // Paleta de concessionárias para o Leaflet
        const coresMalha = {
            'RMP': '#d97706',
            'RMS': '#ea580c',
            'RMN': '#c2410c',
            'RMO': '#9a3412',
            'RMC': '#b45309',
            'EFC': '#dc2626',
            'EFVM': '#991b1b',
            'FCA': '#7c3aed',
            'FNS': '#4338ca',
            'MRS': '#059669',
            'FTL': '#2563eb',
            'FTC': '#0891b2',
            'EFPO': '#65a30d',
            'DEFAULT': '#475569'
        };

        // Dicionário centralizado de cargas
        const DICIONARIO_CARGAS = {
            "Açúcar": ["AÇÚCAR", "ACUCAR"],
            "Milho": ["MILHO"],
            "Grãos e Farelos": ["SOJA", "TRIGO", "CEVADA", "FARELO", "GRÃO", "GRAO", "MALTE"],
            "Óleo Vegetal": ["ÓLEO VEGETAL", "OLEO VEGETAL"],
            "Álcool": ["ÁLCOOL", "ALCOOL", "ETANOL", "ANIDRO"],
            "Combustíveis": ["DIESEL", "GASOLINA", "S10", "S500", "B7", "B100", "BIODIESEL", "PETRÓLEO", "PETROLEO", "ÓLEO COMBUSTÍVEL", "OLEO COMBUST"],
            "Carvão Mineral": ["CARVÃO", "CARVAO", "COQUE", "ANTRACITO"],
            "Ferro Gusa": ["GUSA"],
            "Calcário Siderúrgico": ["CALCÁRIO", "CALCARIO"],
            "Cimento, Cal e Clínquer": ["CIMENTO", "CLÍNQUER", "CLINQUER", "CAL"],
            "Contêiner Cheio de 20 pés": ["CONTAINER", "CONTÊINER", "CONTEINER", "CNTR"],
            "Demais Produtos": ["MINÉRIO", "MINERIO", "SIDERÚRGICO", "SIDERURGICO", "AREIA", "BAUXITA", "CELULOSE", "MADEIRA", "FERTILIZANTE", "FOSFATO", "SAL", "UREIA", "SUCATA", "BOBINA", "VERGALHÃO", "ENXOFRE"]
        };

        function categorizarTerminal(tipoBruto) {
            if (!tipoBruto) return "Demais Produtos";
            const termo = tipoBruto.toUpperCase();
            for (const [categoria_antt, palavrasChave] of Object.entries(DICIONARIO_CARGAS)) {
                if (palavrasChave.some(palavra => termo.includes(palavra))) {
                    return categoria_antt;
                }
            }
            return "Demais Produtos";
        }

        function autoSelecionarCarga(tipoBruto) {
            const cargaExata = categorizarTerminal(tipoBruto);
            const selectCarga = document.getElementById('input-tipo-carga');
            if (cargaExata && selectCarga) {
                selectCarga.value = cargaExata;
                selectCarga.dispatchEvent(new Event('change'));
            }
        }

        function gerarTagsCarga(tipoBruto) {
            if (!tipoBruto || tipoBruto.trim() === '') return '<span class="text-xs text-slate-500 font-medium">Carga Geral</span>';
            const tipos = tipoBruto.split(/[,/]+/).map(t => t.trim()).filter(t => t.length > 0 && t.length <= 25);
            let html = '<span class="text-[11px] text-slate-400 mr-1.5 flex flex-wrap gap-1 items-center">Aceita: ';
            tipos.slice(0, 3).forEach(t => {
                let cor = 'text-slate-500 bg-slate-100';
                const up = t.toUpperCase();
                if (up.includes('SOJA') || up.includes('GRÃO') || up.includes('MILHO')) cor = 'text-lime-700 bg-lime-100';
                else if (up.includes('AÇÚCAR') || up.includes('ACUCAR')) cor = 'text-amber-700 bg-amber-100';
                else if (up.includes('DIESEL') || up.includes('S10') || up.includes('GASOLINA')) cor = 'text-orange-700 bg-orange-100';
                else if (up.includes('MINÉR') || up.includes('FERRO')) cor = 'text-slate-700 bg-slate-200';
                else if (up.includes('CONT') || up.includes('CNTR')) cor = 'text-blue-700 bg-blue-100';
                html += `<span class="px-1.5 py-0.5 rounded ${cor} font-bold text-[10px] uppercase">${t}</span>`;
            });
            html += '</span>';
            return html;
        }

        // Função de Estilo com Tracejado Vermelho para Interrompidos
        // Começa como falso (os troços interrompidos aparecem normais e contínuos, sem vazios e sem vermelho)
        let exibirTracejadas = false;

        function obterEstiloLinha(feature) {
            const sentido = String(feature.properties.sentido || '').trim().toUpperCase();
            const concessionaria = feature.properties.concessionaria || '';

            // Se for interrompido E o utilizador tiver ativado o botão, mostra a tracejada vermelha
            if (sentido === 'INTERROMPIDO' && exibirTracejadas) {
                return {
                    color: '#dc2626',
                    weight: 3,
                    dashArray: '6, 6',
                    opacity: 0.8
                };
            }

            // Caso contrário, pinta normalmente como uma linha operacional contínua
            let corConcessionaria = '#2563eb';
            const upConc = concessionaria.toUpperCase();
            if (upConc.includes('RUMO')) {
                corConcessionaria = '#f59e0b';
            } else if (upConc.includes('VALE') || upConc.includes('EFC')) {
                corConcessionaria = '#dc2626';
            } else if (upConc.includes('VLI') || upConc.includes('FCA')) {
                corConcessionaria = '#8b5cf6';
            } else if (upConc.includes('MRS')) {
                corConcessionaria = '#10b981';
            } else {
                corConcessionaria = coresMalha[upConc] || coresMalha.DEFAULT;
            }

            // dashArray: null é obrigatório: o setStyle do Leaflet mescla as opções,
            // então sem isso o tracejado anterior permanece na linha.
            return {
                color: corConcessionaria,
                weight: 4,
                opacity: 0.9,
                dashArray: null
            };
        }

        function toggleInterrompidasOficial() {
            exibirTracejadas = !exibirTracejadas;
            const luz = document.getElementById('ui-toggle-luz-interrompidas');

            if (exibirTracejadas) {
                luz.className = "w-2 h-2 rounded-full bg-red-600 transition-colors";
            } else {
                luz.className = "w-2 h-2 rounded-full bg-slate-300 transition-colors";
            }

            // Atualiza o estilo instantaneamente na camada existente no mapa (sem recarregar dados)
            if (state.camadaMalha) {
                state.camadaMalha.setStyle(obterEstiloLinha);
            }
        }

        function carregarJson(url) {
            return fetch(url)
                .then(r => r.json())
                .catch(err => { console.error("Erro ao carregar " + url, err); return null; });
        }

        async function inicializarCamadas() {
            // Dispara os dois pedidos em paralelo, mas adiciona na ordem certa:
            // primeiro a malha e depois os terminais, para os pontos ficarem por cima das linhas.
            const pedidoMalha = carregarJson('/dados/malha');
            const pedidoTerminais = carregarJson('/dados/terminais');

            const malha = await pedidoMalha;
            if (malha && malha.features) {
                state.camadaMalha = L.geoJSON(malha, { style: obterEstiloLinha }).addTo(map);
            }

            const terminais = await pedidoTerminais;
            if (terminais && terminais.features) {
                state.camadaTerminais = L.geoJSON(terminais, {
                    pointToLayer: (f, latlng) => L.circleMarker(latlng, { radius: 5, fillColor: "#64748b", color: "#ffffff", weight: 1.5, fillOpacity: 1 }),
                    onEachFeature: (f, layer) => {
                        const nome = f.properties.Terminal || f.properties.terminal || "Terminal Desconhecido";
                        const tipoBruto = f.properties.Tipo || f.properties.tipo || f.properties.TIPO;
                        const tipo = tipoBruto || "Geral";
                        const tooltipContent = `<div style="text-align: center;"><span style="font-size: 12px; font-weight: bold; color: #0f172a;">${nome}</span><br><span style="font-size: 10px; color: #64748b; text-transform: uppercase;">📦 ${tipo}</span></div>`;

                        layer.bindTooltip(tooltipContent, { className: 'custom-tooltip', direction: 'top', offset: [0, -8] });
                        layer.on('click', (e) => {
                            L.DomEvent.stopPropagation(e);
                            if (layer.options.fillColor === '#cbd5e1') return;
                            if (!state.isProcessando) gerenciarSelecao(e.latlng, nome, layer, tipo);
                        });
                    }
                }).addTo(map);
            }
        }

        inicializarCamadas();


        function gerenciarSelecao(latlng, nome, layer, tipo) {
            if (state.pontos.length >= 2) resetarSimulador();
            state.pontos.push(latlng);
            state.nomes.push(nome);

            if (state.pontos.length === 1) {
                layer.setStyle({ fillColor: '#0f172a', radius: 8, weight: 2 });
                layer.bringToFront();
                autoSelecionarCarga(tipo);

                document.getElementById('ui-origem-nome').innerText = nome;
                document.getElementById('ui-origem-cargas').innerHTML = gerarTagsCarga(tipo);
            } else {
                layer.setStyle({ fillColor: '#10b981', radius: 8, weight: 2 });
                layer.bringToFront();
                executarSimulacao();

                document.getElementById('ui-destino-nome').innerText = nome;
                document.getElementById('ui-destino-cargas').innerHTML = gerarTagsCarga(tipo);
            }
            atualizarBannerInstrucao();
        }

        function atualizarBannerInstrucao() {
            const instr = document.getElementById('texto-instrucao');
            const container = document.getElementById('instrucao-simulador');

            if (state.pontos.length === 1) {
                instr.innerHTML = `Origem: <strong class="text-amber-700">${state.nomes[0]}</strong> — Clique no destino`;
                container.className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-amber-50 border border-amber-200 shadow-md transition-all duration-300 text-center";
            } else if (state.pontos.length === 2) {
                instr.innerHTML = "Calculando melhor trajeto...";
                container.className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-blue-50 border border-blue-200 shadow-md transition-all duration-300 text-center animate-pulse text-blue-700";
            }
        }

        function setErroSimulacao(msg) {
            document.getElementById('texto-instrucao').innerHTML = `Erro: ${msg}`;
            document.getElementById('instrucao-simulador').className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-red-50 border border-red-200 shadow-md transition-all duration-300 text-center text-red-700 font-medium";
        }

        async function executarSimulacao() {
            state.isProcessando = true;
            document.getElementById('map-loader').classList.remove('hidden');

            const [p1, p2] = state.pontos;
            const url = `/dados/rota?lat1=${p1.lat}&lng1=${p1.lng}&lat2=${p2.lat}&lng2=${p2.lng}`;

            try {
                const response = await fetch(url);
                const data = await response.json();

                document.getElementById('map-loader').classList.add('hidden');

                if (data.error || !data.features || data.features.length === 0 || data.distancia_total === 0) {
                    setErroSimulacao(data.error || "Rota impossível ou ferrovias desconectadas.");
                    state.isProcessando = false;
                    setTimeout(resetarSimulador, 3500);
                    return;
                }

                state.ultimoData = data;
                desenharRota(data);
                atualizarDashboard(data);
                state.isProcessando = false;

                document.getElementById('texto-instrucao').innerHTML = "Rota traçada com sucesso";
                document.getElementById('instrucao-simulador').className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-emerald-50 border border-emerald-200 shadow-md transition-all duration-300 text-center text-emerald-700 font-medium";

            } catch (error) {
                document.getElementById('map-loader').classList.add('hidden');
                setErroSimulacao("Falha de comunicação com o servidor.");
                state.isProcessando = false;
            }
        }

        let dadosParaSalvar = null;

        function desenharRota(data) {
            if (state.camadaRota) map.removeLayer(state.camadaRota);
            state.camadaRota = L.geoJSON(data, {
                style: {
                    color: '#4f46e5',
                    weight: 6,
                    opacity: 0.9,
                    lineCap: 'round'
                }
            }).addTo(map);

            map.fitBounds(state.camadaRota.getBounds(), { padding: [80, 80] });
        }

        function verificarTransbordos(features) {
            if (!features || features.length < 2) return 0;
            let transbordos = 0;
            const mapaBitolas = {
                'EFC': ['1.60'], 'MRS': ['1.60'], 'FERRONORTE': ['1.60'],
                'NOVOESTE': ['1.00'], 'EFVM': ['1.00'], 'FERROESTE': ['1.00'], 'FTC': ['1.00'], 'ALL': ['1.00'], 'FCA': ['1.00'], 'CFN': ['1.00'],
                'FERROBAN': ['1.00', '1.60'], 'DEFAULT': ['1.00', '1.60']
            };
            let bitolaAtual = null;

            features.forEach(f => {
                const conc = (f.properties.concessionaria || 'DEFAULT').toUpperCase();
                const bitolaTrecho = mapaBitolas[conc] || mapaBitolas['DEFAULT'];

                if (bitolaAtual) {
                    const intersec = bitolaAtual.filter(b => bitolaTrecho.includes(b));
                    if (intersec.length === 0) {
                        transbordos++;
                        bitolaAtual = bitolaTrecho;
                    } else {
                        bitolaAtual = intersec;
                    }
                } else {
                    bitolaAtual = bitolaTrecho;
                }
            });
            return transbordos;
        }

        // --- TARIFA FIXA (por usuário) ---
        let modoTarifa = 'regulada';
        let tarifasFixas = [];

        function cabecalhosJson() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            };
        }

        function definirModoTarifa(modo) {
            modoTarifa = modo;
            const ativo = 'px-3 py-1.5 rounded-md text-xs font-bold bg-white text-slate-900 shadow-sm';
            const inativo = 'px-3 py-1.5 rounded-md text-xs font-bold text-slate-500 hover:text-slate-700';
            document.getElementById('btn-modo-regulada').className = modo === 'regulada' ? ativo : inativo;
            document.getElementById('btn-modo-fixa').className = modo === 'fixa' ? ativo : inativo;
            document.getElementById('btn-editar-tarifas').classList.toggle('hidden', modo !== 'fixa');
            seRotaProntaRecalcular();
        }

        async function abrirModalTarifas() {
            const modal = document.getElementById('modal-tarifas');
            const lista = document.getElementById('lista-tarifas');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            lista.innerHTML = '<p class="text-sm text-slate-400">Carregando...</p>';

            try {
                const r = await fetch('/dados/tarifas-fixas', { headers: { 'Accept': 'application/json' } });
                if (!r.ok) throw new Error(r.status);
                tarifasFixas = await r.json();
                renderizarTarifas();
            } catch (erro) {
                console.error('Erro ao carregar tarifas fixas:', erro);
                lista.innerHTML = '<p class="text-sm text-red-500">Não foi possível carregar as tarifas.</p>';
            }
        }

        function fecharModalTarifas() {
            const modal = document.getElementById('modal-tarifas');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (modoTarifa === 'fixa') seRotaProntaRecalcular();
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !document.getElementById('modal-tarifas').classList.contains('hidden')) {
                fecharModalTarifas();
            }
        });

        function renderizarTarifas() {
            const lista = document.getElementById('lista-tarifas');
            lista.innerHTML = '';

            tarifasFixas.forEach((t, i) => {
                const linha = document.createElement('div');
                linha.className = 'flex items-center gap-2';
                linha.innerHTML = `
                    <span class="flex-1 text-sm text-slate-700">${t.mercadoria}</span>
                    <span class="text-xs text-slate-400">R$/t</span>
                    <input type="number" min="0" step="0.01" inputmode="decimal" placeholder="—"
                        value="${t.valor ?? ''}" onchange="salvarTarifaFixa(${i}, this)"
                        class="w-28 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-slate-800 text-right focus:ring-2 focus:ring-amber-500 outline-none">
                    <button type="button" onclick="restaurarTarifaFixa(${i})" title="Voltar ao valor padrão"
                        class="btn-restaurar ${t.personalizada ? '' : 'invisible'} text-[10px] font-bold uppercase text-slate-500 hover:text-slate-800">Restaurar</button>`;
                lista.appendChild(linha);
            });
        }

        async function salvarTarifaFixa(i, input) {
            const t = tarifasFixas[i];
            const valor = parseFloat(input.value);

            if (isNaN(valor) || valor < 0) {
                input.value = t.valor ?? '';
                return;
            }

            input.disabled = true;
            try {
                const r = await fetch('/dados/tarifas-fixas', {
                    method: 'PUT',
                    headers: cabecalhosJson(),
                    body: JSON.stringify({ mercadoria: t.mercadoria, valor: valor })
                });
                if (!r.ok) throw new Error(r.status);
                t.valor = valor;
                t.personalizada = true;
                input.closest('div').querySelector('.btn-restaurar').classList.remove('invisible');
            } catch (erro) {
                console.error('Erro ao salvar tarifa fixa:', erro);
                input.value = t.valor ?? '';
                alert('Não foi possível salvar a tarifa.');
            } finally {
                input.disabled = false;
            }
        }

        async function restaurarTarifaFixa(i) {
            const t = tarifasFixas[i];
            try {
                const r = await fetch('/dados/tarifas-fixas', {
                    method: 'DELETE',
                    headers: cabecalhosJson(),
                    body: JSON.stringify({ mercadoria: t.mercadoria })
                });
                if (!r.ok) throw new Error(r.status);
                t.valor = t.padrao;
                t.personalizada = false;
                renderizarTarifas();
            } catch (erro) {
                console.error('Erro ao restaurar tarifa fixa:', erro);
                alert('Não foi possível restaurar a tarifa.');
            }
        }

        function seRotaProntaRecalcular() {
            if (state.ultimoData) atualizarDashboard(state.ultimoData);
        }

        async function calcularFreteDinamico(distanciaDaRota, mercadoriaEscolhida, toneladasDigitadas, concessionariaDoTrecho) {
            const dadosTrajeto = {
                distanciaKm: distanciaDaRota,
                mercadoria: mercadoriaEscolhida,
                toneladas: toneladasDigitadas,
                concessionaria: concessionariaDoTrecho,
                modo: modoTarifa
            };

            try {
                const resposta = await fetch('/dados/calcular-frete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dadosTrajeto)
                });

                const resultado = await resposta.json();
                if (resultado.erro) {
                    document.getElementById('res-frete').innerText = (modoTarifa === 'fixa') ? "Defina a tarifa" : "Indisponível";
                    return;
                }

                const formatBRL = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                document.getElementById('res-litros').innerText = Math.round(resultado.litros).toLocaleString('pt-BR') + ' L';
                document.getElementById('res-frete').innerText = formatBRL(resultado.custoFrete);
                document.getElementById('res-economia').innerText = formatBRL(resultado.economia);

                dadosParaSalvar = {
                    origem: state.nomes[0],
                    destino: state.nomes[state.nomes.length - 1],
                    mercadoria: mercadoriaEscolhida,
                    toneladas: toneladasDigitadas,
                    distancia_km: distanciaDaRota,
                    custo_frete: resultado.custoFrete,
                    economia_co2: (distanciaDaRota * toneladasDigitadas * 0.04442)
                };
                document.getElementById('btn-salvar').classList.remove('hidden');

            } catch (erro) {
                console.error("Erro ao calcular frete:", erro);
            }
        }

        function atualizarDashboard(data) {
            const dist = parseFloat(data.distancia_total) || 0;
            const tu = parseFloat(document.getElementById('input-carga').value) || 5000;
            const mercadoriaEscolhida = document.getElementById('input-tipo-carga').value;

            let concessionariaTrecho = "RMP";
            if (data.features && data.features.length > 0) {
                concessionariaTrecho = (data.features[0].properties.concessionaria || "RMP").toUpperCase();
            }

            const transbordos = verificarTransbordos(data.features);
            const penalidadeTempo = transbordos * 24;
            const tku = dist * tu;
            const tempoTotal = (dist / 35) + penalidadeTempo;

            document.getElementById('res-km').innerText = dist.toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

            const horas = Math.floor(tempoTotal);
            const minutos = Math.round((tempoTotal % 1) * 60);
            document.getElementById('res-tempo').innerHTML = `${horas}h ${minutos}m ${transbordos > 0 ? '<br><span class="text-red-500 text-xs">(' + transbordos + ' transbordos)</span>' : ''}`;

            const economiaCO2 = tku * 0.04442;
            document.getElementById('res-co2').innerText = economiaCO2.toLocaleString('pt-BR', { maximumFractionDigits: 0 });

            document.getElementById('dashboard-custos').classList.remove('opacity-40', 'grayscale', 'pointer-events-none');
            renderizarManifesto(data.terminais_percorridos, transbordos);

            if (mercadoriaEscolhida !== "") {
                calcularFreteDinamico(dist, mercadoriaEscolhida, tu, concessionariaTrecho);
            } else {
                document.getElementById('res-frete').innerText = "Selecione a carga";
            }
        }

        function renderizarManifesto(terminais, transbordos) {
            const lista = document.getElementById('lista-percurso');
            lista.innerHTML = '';

            const alerta = document.getElementById('alerta-transbordo');
            if (transbordos > 0) {
                document.getElementById('num-transbordos').innerText = transbordos;
                alerta.classList.remove('hidden');
            } else {
                alerta.classList.add('hidden');
            }

            document.getElementById('empty-state').classList.add('hidden');
            document.getElementById('timeline-wrapper').classList.remove('hidden');
            document.getElementById('btn-reset').classList.remove('hidden');

            let listaNomes = terminais && terminais.length > 0 ? terminais : [state.nomes[0], state.nomes[1]];

            listaNomes.forEach((nomeTerminal, index) => {
                const isOrigem = index === 0;
                const isDestino = index === listaNomes.length - 1;
                const isExtremidade = isOrigem || isDestino;

                let label = "Ponto de Passagem";
                if (isOrigem) label = "Terminal de Origem";
                if (isDestino) label = "Destino Final";

                const li = document.createElement('li');
                li.className = `timeline-item flex flex-col ${isExtremidade ? 'endpoint' : ''}`;
                li.innerHTML = `<span class="text-[10px] uppercase tracking-wider font-bold ${isExtremidade ? 'text-amber-600' : 'text-slate-400'}">${label}</span><span class="text-xs ${isExtremidade ? 'font-bold text-slate-800' : 'font-medium text-slate-600'} leading-tight mt-0.5">${nomeTerminal}</span>`;
                lista.appendChild(li);
            });
        }

        function resetarSimulador() {
            if (state.isProcessando) return;
            state.pontos = []; state.nomes = []; state.ultimoData = null;
            if (state.camadaRota) map.removeLayer(state.camadaRota);
            if (state.camadaTerminais) {
                state.camadaTerminais.eachLayer(layer => { layer.setStyle({ fillColor: "#64748b", color: "#ffffff", radius: 5, weight: 1.5, opacity: 1, fillOpacity: 1 }); });
            }
            document.getElementById('timeline-wrapper').classList.add('hidden');
            document.getElementById('empty-state').classList.remove('hidden');
            document.getElementById('btn-reset').classList.add('hidden');
            document.getElementById('alerta-transbordo').classList.add('hidden');
            document.getElementById('dashboard-custos').classList.add('opacity-40', 'grayscale', 'pointer-events-none');
            document.getElementById('res-km').innerText = "0.0";
            document.getElementById('res-tempo').innerText = "--";
            document.getElementById('btn-salvar').classList.add('hidden');

            const container = document.getElementById('instrucao-simulador');
            container.className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-white border border-slate-200 shadow-md transition-all duration-300 text-center";
            document.getElementById('texto-instrucao').innerHTML = `Clique em um terminal de <strong class="text-slate-900">ORIGEM</strong> para iniciar`;
        }

        let camadaPlaneada = null;

        function toggleMalhaOficial() {
            if (!state.camadaMalha || !state.camadaTerminais) return;

            const luz = document.getElementById('ui-toggle-luz');
            const texto = document.getElementById('ui-toggle-texto');

            if (state.malhaVisivel) {
                map.removeLayer(state.camadaMalha);
                map.removeLayer(state.camadaTerminais);
                state.malhaVisivel = false;
                luz.classList.replace('bg-emerald-500', 'bg-slate-300');
                texto.innerText = "Malha Nacional Oculta";
            } else {
                state.camadaMalha.addTo(map);
                state.camadaTerminais.addTo(map);
                state.malhaVisivel = true;
                luz.classList.replace('bg-slate-300', 'bg-emerald-500');
                texto.innerText = "Malha Nacional Ativa";
            }
        }

        function carregarMalhaPersonalizada(event) {
            const ficheiro = event.target.files[0];
            if (!ficheiro) return;
            const reader = new FileReader();

            reader.onload = function (e) {
                try {
                    const geojsonDados = JSON.parse(e.target.result);
                    if (camadaPlaneada) map.removeLayer(camadaPlaneada);

                    camadaPlaneada = L.geoJSON(geojsonDados, {
                        style: {
                            color: '#e11d48',
                            weight: 4,
                            dashArray: '8, 8',
                            lineCap: 'round',
                            opacity: 0.9
                        }
                    }).addTo(map);

                    map.fitBounds(camadaPlaneada.getBounds(), { padding: [50, 50] });
                    document.getElementById('texto-instrucao').innerHTML = `<strong class="text-emerald-700">Malha personalizada carregada com sucesso!</strong>`;
                    document.getElementById('instrucao-simulador').className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-emerald-50 border border-emerald-200 shadow-md transition-all duration-300 text-center";
                } catch (erro) {
                    console.error("Erro ao ler o ficheiro:", erro);
                    alert("Não foi possível ler este ficheiro. Confirme se é um GeoJSON válido.");
                }
            };
            reader.readAsText(ficheiro);
            event.target.value = '';
        }

        async function salvarSimulacao() {
            if (!dadosParaSalvar) return;
            const btn = document.getElementById('btn-salvar');
            btn.innerHTML = 'Salvando...';
            btn.disabled = true;

            try {
                const resposta = await fetch('/simulador/salvar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dadosParaSalvar)
                });

                const json = await resposta.json();
                if (json.status === 'sucesso') {
                    btn.innerHTML = 'Salvo no Dashboard!';
                    btn.classList.replace('bg-emerald-600', 'bg-slate-800');
                }
            } catch (erro) {
                console.error("Erro ao salvar:", erro);
                btn.innerHTML = 'Erro ao salvar';
                btn.classList.replace('bg-emerald-600', 'bg-red-600');
            }
        }

    </script>
@endsection