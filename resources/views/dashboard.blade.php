@extends('layouts.app')

@section('titulo', 'Meu Dashboard')

@section('conteudo')
    <div class="max-w-[1200px] mx-auto px-4 mt-8 mb-12 font-sans">

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Meu Painel</h1>
                <p class="text-sm text-slate-500 mt-1">Histórico de rotas simuladas no Rail Brazil.</p>
            </div>
            <a href="{{ route('simulador') }}"
                class="bg-amber-500 text-slate-900 font-bold px-5 py-2.5 rounded-lg hover:bg-amber-400 transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Simulação
            </a>
        </div>

        @if(session('sucesso'))
            <div
                class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="font-bold text-sm">{{ session('sucesso') }}</span>
            </div>
        @endif

        <!-- Tabela de Simulações -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

            @if($simulacoes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase tracking-wider text-slate-500">
                                <th class="py-4 px-6 font-bold">Data</th>
                                <th class="py-4 px-6 font-bold">Origem <span class="text-slate-300 mx-1">➔</span> Destino</th>
                                <th class="py-4 px-6 font-bold">Carga & Volume</th>
                                <th class="py-4 px-6 font-bold">Distância</th>
                                <th class="py-4 px-6 font-bold text-right">Custo Estimado</th>
                                <th class="py-4 px-6"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">

                            @foreach($simulacoes as $simulacao)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-6 text-slate-500 font-medium whitespace-nowrap">
                                        {{ $simulacao->created_at->format('d/m/Y') }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800">{{ $simulacao->origem }}</span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                            <span class="font-bold text-slate-800">{{ $simulacao->destino }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        <span class="block font-semibold text-slate-700">{{ $simulacao->mercadoria }}</span>
                                        <span class="text-xs text-slate-500">{{ number_format($simulacao->toneladas, 0, '', '.') }}
                                            Toneladas</span>
                                    </td>

                                    <td class="py-4 px-6 text-slate-600 font-medium">
                                        {{ number_format($simulacao->distancia_km, 1, ',', '.') }} km
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <span class="block font-black text-amber-600">R$
                                            {{ number_format($simulacao->custo_frete, 2, ',', '.') }}</span>
                                        @if($simulacao->economia_co2 > 0)
                                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mt-0.5 block">
                                                ↓ {{ number_format($simulacao->economia_co2, 0, '', '.') }}kg CO₂
                                            </span>
                                        @endif
                                    </td>
                                    <!-- BOTÃO DE EXCLUIR -->
                                    <td class="py-4 px-6 text-right">
                                        <form action="{{ route('simulador.excluir', $simulacao->id) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta simulação?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-slate-400 hover:text-red-500 transition-colors p-2 rounded-md hover:bg-red-50"
                                                title="Excluir Simulação">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            @else
                <!-- Estado Vazio (Sem simulações) -->
                <div class="py-16 px-6 text-center flex flex-col items-center">
                    <div
                        class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Nenhuma simulação salva</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">Você ainda não salvou nenhuma rota. Acesse o
                        simulador, trace um percurso e clique em "Salvar no Dashboard" para criar o seu histórico.</p>
                    <a href="{{ route('simulador') }}"
                        class="text-sm font-bold text-amber-600 hover:text-amber-700 hover:underline">Ir para o Simulador
                        &rarr;</a>
                </div>
            @endif

        </div>
    </div>
@endsection