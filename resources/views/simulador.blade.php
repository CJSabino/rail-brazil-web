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
            height: 750px;
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

                    <!-- Overlay Top (Informativo) -->
                    <div
                        class="absolute top-0 left-0 right-0 z-[1000] p-4 bg-gradient-to-b from-black/20 to-transparent pointer-events-none">
                        <div
                            class="inline-flex items-center gap-3 px-4 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm pointer-events-none">
                            <div class="flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                <span class="font-mono text-[10px] uppercase text-slate-500 tracking-wider">Malha Nacional
                                    Ativa</span>
                            </div>
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
                        </ul>
                    </div>
                </div>
            </div>

            <!-- PAINEL LATERAL -->
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

                    <button onclick="resetarSimulador()" id="btn-reset"
                        class="mt-4 w-full py-3 bg-slate-900 text-white text-sm font-bold rounded-lg hover:bg-slate-800 transition-colors hidden shadow-sm">
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // --- LÓGICA DO MAPA E API --
        const map = L.map('map', { zoomControl: false }).setView([-15.78, -47.92], 4);
        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Esri',
            maxZoom: 16
        }).addTo(map);

        const state = { pontos: [], nomes: [], tipoOrigem: null, camadaRota: null, camadaTerminais: null, isProcessando: false, ultimoData: null };

        // Paleta de concessionárias para o Leaflet
        const coresMalha = {
            // Rumo )
            'RMP': '#d97706', // amber-600 
            'RMS': '#ea580c', // orange-600
            'RMN': '#c2410c', // orange-700
            'RMO': '#9a3412', // orange-800
            'RMC': '#b45309', // amber-700

            // Vale
            'EFC': '#dc2626', // red-600
            'EFVM': '#991b1b', // red-800

            // VLI 
            'FCA': '#7c3aed', // violet-600
            'FNS': '#4338ca', // indigo-700

            // Independentes 
            'MRS': '#059669', // emerald-600
            'FTL': '#2563eb', // blue-600
            'FTC': '#0891b2', // cyan-600
            'EFPO': '#65a30d', // lime-600

            'DEFAULT': '#475569' // slate-600
        };

        // Dicionário centralizado de categorias
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
            // TUDO que for minério, siderurgia, madeira ou fertilizante que não tem tarifa específica, cai na regra geral:
            "Demais Produtos": ["MINÉRIO", "MINERIO", "SIDERÚRGICO", "SIDERURGICO", "AREIA", "BAUXITA", "CELULOSE", "MADEIRA", "FERTILIZANTE", "FOSFATO", "SAL", "UREIA", "SUCATA", "BOBINA", "VERGALHÃO", "ENXOFRE"]
        };

        //Função de categorização direta
        function categorizarTerminal(tipoBruto) {
            if (!tipoBruto) return "Demais Produtos";
            const termo = tipoBruto.toUpperCase();

            // Procura a palavra-chave. Se achar, retorna exatamente o nome do Option do Select
            for (const [categoria_antt, palavrasChave] of Object.entries(DICIONARIO_CARGAS)) {
                if (palavrasChave.some(palavra => termo.includes(palavra))) {
                    return categoria_antt;
                }
            }
            return "Demais Produtos"; // Fallback seguro
        }

        // Auto-selecionar no painel sem intermediários
        function autoSelecionarCarga(tipoBruto) {
            const cargaExata = categorizarTerminal(tipoBruto);
            const selectCarga = document.getElementById('input-tipo-carga');

            if (cargaExata && selectCarga) {
                selectCarga.value = cargaExata;
                // Força o frontend a recalcular o frete
                selectCarga.dispatchEvent(new Event('change'));
            }
        }

        function gerarTagsCarga(tipoBruto) {
            if (!tipoBruto || tipoBruto.trim() === '') return '<span class="text-xs text-slate-500 font-medium">Carga Geral</span>';

            // Separa os nomes misturados
            const tipos = tipoBruto.split(/[,/]+/).map(t => t.trim()).filter(t => t.length > 0 && t.length <= 25);

            let html = '<span class="text-[11px] text-slate-400 mr-1.5 flex flex-wrap gap-1 items-center">Aceita: ';

            tipos.slice(0, 3).forEach(t => { // Limita a 3 tags para não quebrar o layout
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

        // Filtro de Mapa
        function filtrarTerminaisCompativeis(tipoOrigem, layerOrigem) {
            state.camadaTerminais.eachLayer(layer => {
                if (layer === layerOrigem) return;

                // Pega a coluna correta do seu CSV/Banco
                const tipoDestino = layer.feature.properties.Tipo || layer.feature.properties.tipo || layer.feature.properties.TIPO || "Desconhecido";

                // Se a origem e o destino caírem na mesma categoria do seu Select, eles são compatíveis
                if (categorizarTerminal(tipoDestino) === categorizarTerminal(tipoOrigem)) {
                    layer.setStyle({ fillColor: "#fde047", color: "#0f172a", weight: 2, radius: 7, opacity: 1, fillOpacity: 1 });
                    layer.bringToFront();
                } else {
                    layer.setStyle({ fillColor: "#f1f5f9", color: "#cbd5e1", weight: 1, radius: 4, opacity: 0.4, fillOpacity: 0.4 });
                }
            });
        }

        async function inicializarCamadas() {
            try {
                fetch('/api/malha').then(r => r.json()).then(data => {
                    if (!data || !data.features) return;
                    L.geoJSON(data, {
                        style: (f) => ({
                            color: coresMalha[f.properties.concessionaria?.toUpperCase()] || coresMalha.DEFAULT,
                            weight: 3.5,
                            opacity: 0.9,
                            lineCap: 'round'
                        })
                    }).addTo(map);
                });

                fetch('/api/terminais').then(r => r.json()).then(data => {
                    if (!data || !data.features) return;
                    state.camadaTerminais = L.geoJSON(data, {
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
                });
            } catch (error) { console.error("Erro fatal:", error); }
        }

        inicializarCamadas();

        function gerenciarSelecao(latlng, nome, layer, tipo) {
            if (state.pontos.length >= 2) resetarSimulador();
            state.pontos.push(latlng);
            state.nomes.push(nome);

            if (state.pontos.length === 1) {
                layer.setStyle({ fillColor: '#0f172a', radius: 8, weight: 2 });
                layer.bringToFront();
                //filtrarTerminaisCompativeis(tipo, layer);
                autoSelecionarCarga(tipo);

                //Atualiza a Origem na tela
                document.getElementById('ui-origem-nome').innerText = nome;
                document.getElementById('ui-origem-cargas').innerHTML = gerarTagsCarga(tipo);

            } else {
                layer.setStyle({ fillColor: '#10b981', radius: 8, weight: 2 });
                layer.bringToFront();
                executarSimulacao();

                //Atualiza o Destino na tela
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
            const url = `/api/rota?lat1=${p1.lat}&lng1=${p1.lng}&lat2=${p2.lat}&lng2=${p2.lng}`;

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

        function seRotaProntaRecalcular() {
            if (state.ultimoData) atualizarDashboard(state.ultimoData);
        }

        async function calcularFreteDinamico(distanciaDaRota, mercadoriaEscolhida, toneladasDigitadas, concessionariaDoTrecho) {
            const dadosTrajeto = {
                distanciaKm: distanciaDaRota,
                mercadoria: mercadoriaEscolhida,
                toneladas: toneladasDigitadas,
                concessionaria: concessionariaDoTrecho
            };

            try {
                const resposta = await fetch('/api/calcular-frete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dadosTrajeto)
                });

                const resultado = await resposta.json();

                if (resultado.erro) {
                    document.getElementById('res-frete').innerText = "Indisponível";
                    return;
                }

                const formatBRL = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                document.getElementById('res-litros').innerText = Math.round(resultado.litros).toLocaleString('pt-BR') + ' L';
                document.getElementById('res-frete').innerText = formatBRL(resultado.custoFrete);
                document.getElementById('res-economia').innerText = formatBRL(resultado.economia);

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

            const container = document.getElementById('instrucao-simulador');
            container.className = "absolute bottom-8 left-1/2 -translate-x-1/2 z-[1000] px-5 py-2.5 rounded-full bg-white border border-slate-200 shadow-md transition-all duration-300 text-center";
            document.getElementById('texto-instrucao').innerHTML = `Clique em um terminal de <strong class="text-slate-900">ORIGEM</strong> para iniciar`;
        }
    </script>
@endsection