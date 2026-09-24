@extends('layouts.app')

@section('titulo', 'Início')

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-top {
            top: 20px;
        }
    </style>
@endsection

@section('conteudo')
    @php
        $heroImg = "https://images.unsplash.com/photo-1661518549927-2e5d9b4afde7?w=1800&h=900&fit=crop&auto=format";

        $stats = [
            ['value' => '30452', 'display' => '30.452', 'unit' => 'km', 'label' => 'Malha ferroviária nacional'],
            ['value' => '6', 'display' => '6', 'unit' => '', 'label' => 'Concessionárias ativas'],
            ['value' => '15', 'display' => '15', 'unit' => '', 'label' => 'Terminais mapeados'],
            ['value' => '1200', 'display' => '1.2bi', 'unit' => 't/ano', 'label' => 'Capacidade total de carga'],
        ];

        $concessionaires = [
            ['name' => 'VALE', 'lines' => 'EFC · EFVM', 'km' => '1.797 km', 'color' => '#2563eb'],
            ['name' => 'Rumo Logística', 'lines' => 'Malha Norte · Sul · Paulista · Oeste', 'km' => '11.887 km', 'color' => '#d97706'],
            ['name' => 'MRS Logística', 'lines' => 'Rede Sudeste', 'km' => '1.643 km', 'color' => '#059669'],
            ['name' => 'VLI', 'lines' => 'FCA · Norte-Sul · Centro-Leste', 'km' => '7.638 km', 'color' => '#7c3aed'],
            ['name' => 'Transnordestina', 'lines' => 'FTL', 'km' => '4.238 km', 'color' => '#db2777'],
            ['name' => 'EFPO', 'lines' => 'Malha Oeste Pioneira', 'km' => '2.249 km', 'color' => '#0891b2'],
        ];

        $miningImg = "https://images.unsplash.com/photo-1654461339694-128902c5c075?w=800&h=500&fit=crop&auto=format";
        $grainImg = "https://images.unsplash.com/photo-1600747476236-76579658b1b1?w=800&h=500&fit=crop&auto=format";
        $factoryImg = "https://images.unsplash.com/photo-1659291457360-13ef34276765?w=800&h=500&fit=crop&auto=format";

        $cargas = [
            ['img' => $miningImg, 'tag' => 'Minério de Ferro', 'pct' => '64%', 'desc' => 'Principal carga da malha ferroviária nacional, escoada pelas ferrovias da VALE desde as minas de Carajás e do Quadrilátero Ferrífero.', 'color' => '#64748b'],
            ['img' => $grainImg, 'tag' => 'Grãos & Soja', 'pct' => '21%', 'desc' => 'Produção do cerrado brasileiro escoada pela Malha Norte e pela FCA, conectando Mato Grosso e Goiás aos portos do Atlântico.', 'color' => '#65a30d'],
            ['img' => $factoryImg, 'tag' => 'Combustíveis', 'pct' => '15%', 'desc' => 'Distribuição de combustíveis e derivados de petróleo integrada à malha ferroviária para redução de custos logísticos.', 'color' => '#ea580c'],
        ];

    @endphp

    <!-- HERO -->
    <section class="relative min-h-[90vh] flex items-end overflow-hidden bg-slate-900 -mt-16">
        <div class="absolute inset-0"
            style="background-image: url('{{ $heroImg }}'); background-size: cover; background-position: center 40%;"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/55 to-slate-900/10"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 pb-24 pt-40 w-full">
            <div class="max-w-3xl">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-amber-400/40 bg-amber-400/10 mb-6">
                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                    <span class="text-xs font-mono text-amber-400 tracking-widest uppercase">Plataforma Oficial ·
                        2026</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-bold text-white leading-[1.05] tracking-tight mb-6">
                    Infraestrutura<br />
                    <span class="text-amber-400">Ferroviária</span><br />
                    do Brasil
                </h1>
                <p class="text-lg text-slate-300 max-w-xl mb-10 leading-relaxed">
                    Visualize, analise e simule trajetos sobre a malha ferroviária nacional. Dados operacionais em tempo
                    real de todas as concessões ativas.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('simulador') }}"
                        class="inline-flex items-center gap-3 px-7 py-3.5 rounded-lg bg-amber-400 text-slate-900 font-bold text-sm hover:bg-amber-300 transition-colors">
                        Explorar Mapa →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-20">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-16">
                @foreach($stats as $s)
                    <div class="flex flex-col gap-1 stat-card">
                        <div class="font-sans text-4xl lg:text-5xl font-bold text-slate-900 tracking-tight">
                            <span class="counter" data-target="{{ $s['value'] }}" data-display="{{ $s['display'] }}">0</span>
                            @if($s['unit'])
                                <span class="text-amber-600 text-2xl lg:text-3xl ml-1">{{ $s['unit'] }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-500">{{ $s['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CONCESSIONÁRIAS -->
    <section class="bg-[#f8f7f4] border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-20">
            <div class="flex items-end justify-between mb-12 gap-4">
                <div>
                    <p class="text-xs font-mono text-amber-600 tracking-widest uppercase mb-3">Operadores</p>
                    <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">Concessionárias</h2>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($concessionaires as $c)
                    <div
                        class="group relative p-5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 hover:shadow-md transition-all overflow-hidden cursor-default">
                        <div class="absolute top-0 left-0 right-0 h-0.5" style="background: {{ $c['color'] }}"></div>
                        <div class="font-bold text-slate-900 mb-1">{{ $c['name'] }}</div>
                        <div class="text-xs text-slate-400 mb-4 leading-relaxed">{{ $c['lines'] }}</div>
                        <div class="flex items-end justify-between">
                            <span class="font-mono text-2xl font-bold" style="color: {{ $c['color'] }}">{{ $c['km'] }}</span>
                            <span class="text-xs text-slate-400">extensão</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CARGAS -->
    <section class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-20">
            <p class="text-xs font-mono text-amber-600 tracking-widest uppercase mb-3">Cargas</p>
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight mb-12">Principais Produtos Transportados
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($cargas as $item)
                    <div class="group rounded-xl overflow-hidden border border-slate-200 hover:shadow-lg transition-all">
                        <div class="relative h-48 overflow-hidden bg-slate-100">
                            <img src="{{ $item['img'] }}" alt="{{ $item['tag'] }}"
                                class="w-full h-full object-cover opacity-80 group-hover:opacity-95 group-hover:scale-105 transition-all duration-500" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent"></div>
                            <div class="absolute top-3 left-3 px-2.5 py-1 rounded text-xs font-semibold text-white"
                                style="background: {{ $item['color'] }}cc">{{ $item['tag'] }}</div>
                            <div class="absolute bottom-3 right-3 font-bold text-3xl text-white font-mono drop-shadow">
                                {{ $item['pct'] }}
                            </div>
                        </div>
                        <div class="p-4 bg-white">
                            <p class="text-sm text-slate-500 leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- NEWS -->
    <section class="bg-[#f8f7f4] border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-20">
            <p class="text-xs font-mono text-amber-600 tracking-widest uppercase mb-3">Atualizações</p>
            <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight mb-12">Últimas Notícias</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($news as $n)
                    <a href="{{ $n['link'] }}" target="_blank" rel="noopener noreferrer"
                        class="group flex flex-col border border-slate-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-slate-300 transition-all bg-white cursor-pointer">
                        <div class="relative h-40 bg-slate-100 overflow-hidden">
                            <img src="{{ $n['img'] }}" alt="{{ $n['title'] }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-all duration-500" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent"></div>
                        </div>
                        <div class="flex-1 p-5 flex flex-col gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono text-slate-400">{{ $n['date'] }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span class="text-xs font-semibold text-amber-600">{{ $n['tag'] }}</span>
                            </div>
                            <h3
                                class="font-semibold text-slate-800 leading-snug text-sm group-hover:text-amber-600 transition-colors">
                                {{ $n['title'] }}
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed flex-1">{{ $n['excerpt'] }}</p>

                            <div
                                class="mt-2 pt-3 border-t border-slate-100 flex items-center justify-end text-xs font-bold text-amber-600 gap-1 group-hover:gap-2 transition-all">
                                <span>Acessar portal</span><span>→</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- MAPA INTERATIVO -->
    <section id="simulador"
        class="relative w-full h-[70vh] min-h-[600px] bg-slate-200 border-t border-slate-300 z-10 mt-12">
        <!-- Div onde o Leaflet vai renderizar -->
        <div id="map" class="absolute inset-0 w-full h-full z-10"></div>

        <!-- Painel de Controle -->
        <div
            class="absolute top-6 left-6 z-[400] bg-white/95 backdrop-blur p-5 rounded-xl shadow-xl border border-slate-200 w-80 md:w-96 pointer-events-auto">
            <div
                class="inline-flex items-center gap-2 px-2 py-1 rounded bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-widest mb-3">
                Simulador Ativo
            </div>
            <h3 class="font-bold text-slate-900 text-xl mb-2 tracking-tight">Roteamento Integrado</h3>
            <p class="text-sm text-slate-500 mb-5 leading-relaxed">
                Visualize a malha e informações basicas direto no mapa clickando.
            </p>

            <div
                class="p-4 border-2 border-dashed border-slate-200 rounded-lg text-center text-sm text-slate-400 font-mono">
                <!-- Filtro de Concessionárias -->
                <div class="mt-4">
                    <label for="filtro-concessionaria"
                        class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">
                        Visualizar Malha
                    </label>
                    <select id="filtro-concessionaria"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm font-medium rounded-lg focus:ring-amber-500 focus:border-amber-500 block p-3 outline-none transition-all cursor-pointer shadow-sm">
                        <option value="TODAS">Todas as Concessionárias</option>
                        <option value="RMN">Rumo Malha Norte (RMN)</option>
                        <option value="RMO">Rumo Malha Oeste (RMO)</option>
                        <option value="RMP">Rumo Malha Paulista (RMP)</option>
                        <option value="RMS">Rumo Malha Sul (RMS)</option>
                        <option value="EFC">Estrada de Ferro Carajás (EFC)</option>
                        <option value="EFVM">Estrada de Ferro Vitória a Minas (EFVM)</option>
                        <option value="FCA">Ferrovia Centro-Atlântica (FCA)</option>
                        <option value="FNS">Ferrovia Norte-Sul (FNS)</option>
                        <option value="MRS">MRS Logística</option>
                        <option value="FTL">Ferrovia Transnordestina (FTL)</option>
                    </select>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Inicializa o mapa base
        const map = L.map('map').setView([-15.7801, -47.9292], 4);

        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ',
            maxZoom: 16
        }).addTo(map);

        // Variáveis da Malha
        let geojsonData = null;
        let camadaAtual = null;

        // Cores das concessionárias
        const cores = {
            'RMN': '#16a34a', 'RMO': '#22c55e', 'RMP': '#4ade80', 'RMS': '#15803d',
            'EFPO': '#86efac', 'EFC': '#dc2626', 'EFVM': '#991b1b', 'FCA': '#f97316',
            'FNS': '#fb923c', 'MRS': '#eab308', 'FTL': '#2563eb', 'FTC': '#6366f1',
            'DEFAULT': '#94a3b8'
        };

        // Função de renderização e filtro
        function renderizarMapa(filtro) {
            if (!geojsonData) return;
            if (camadaAtual) map.removeLayer(camadaAtual);

            camadaAtual = L.geoJSON(geojsonData, {
                filter: function (feature) {
                    if (filtro === 'TODAS') return true;
                    return (feature.properties.concessionaria || "").toUpperCase().includes(filtro);
                },
                style: function (feature) {
                    return {
                        color: cores[(feature.properties.concessionaria || "").toUpperCase()] || cores['DEFAULT'],
                        weight: 3,
                        opacity: 0.85,
                        lineJoin: 'round'
                    };
                },
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(`<div style="font-family: Inter, sans-serif;">
                                <b style="color: #0f172a; font-size: 14px;">${feature.properties.linha || 'Trecho Ferroviário'}</b><br>
                                <span style="color: #64748b; font-size: 12px;">Operador: ${feature.properties.concessionaria}</span>
                            </div>`);

                    // Efeito Hover
                    layer.on('mouseover', function () { this.setStyle({ weight: 6, opacity: 1 }); });
                    layer.on('mouseout', function () { this.setStyle({ weight: 3, opacity: 0.85 }); });
                }
            }).addTo(map);
        }

        // Busca os dados da API Laravel e renderiza inicial
        fetch('/api/malha')
            .then(response => response.json())
            .then(data => {
                geojsonData = data;
                renderizarMapa('TODAS');
            })
            .catch(error => console.error("Erro ao carregar a malha ferroviária:", error));

        // Interação com o Select de Filtro
        document.getElementById('filtro-concessionaria').addEventListener('change', function (e) {
            renderizarMapa(e.target.value);
        });
    </script>
    <script>
        // Animação dos Números (Contador)
        const counters = document.querySelectorAll('.counter');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'));
                    const finalDisplay = el.getAttribute('data-display');
                    let current = 0;
                    const increment = target / 50;

                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            el.innerText = Math.ceil(current);
                            requestAnimationFrame(updateCounter);
                        } else {
                            el.innerText = finalDisplay;
                        }
                    };
                    updateCounter();
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.3 });

        counters.forEach(counter => observer.observe(counter));

    </script>
@endsection