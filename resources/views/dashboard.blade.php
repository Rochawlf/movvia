<x-app-layout>
    <div class="h-screen bg-gray-50 flex flex-col overflow-hidden" x-data="{
        mapMode: 'default',
        driverOnline: @js((bool) auth()->user()->is_online)
    }" @map-mode-changed.window="mapMode = $event.detail"
        @driver-status-updated.window="driverOnline = $event.detail.isOnline">

        {{-- ========================= --}}
        {{-- MENU GLOBAL / HAMBÚRGUER --}}
        {{-- ========================= --}}
        <div x-data="{ sideMenuOpen: false }" class="z-[180] absolute top-0 left-0">

            {{-- BOTÃO HAMBÚRGUER --}}
            <button x-show="mapMode !== 'mapSelection'" x-transition:enter="transition ease-out duration-300 delay-200"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                @click="sideMenuOpen = true"
                class="absolute top-4 left-4 w-12 h-12 bg-white/95 backdrop-blur-xl rounded-2xl shadow-xl border border-white/60 flex flex-col items-center justify-center gap-1.5 z-50 hover:bg-white transition-all active:scale-95">
                <div class="w-6 h-0.5 bg-gray-800 rounded-full"></div>
                <div class="w-6 h-0.5 bg-gray-800 rounded-full"></div>
                <div class="w-4 h-0.5 bg-gray-800 rounded-full self-start ml-3"></div>
            </button>

            {{-- BACKDROP MENU --}}
            <div x-show="sideMenuOpen" x-cloak @click="sideMenuOpen = false"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[100]"></div>

            {{-- DRAWER MENU --}}
            <div x-show="sideMenuOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed top-0 left-0 bottom-0 w-[85%] max-w-[340px] bg-white shadow-2xl z-[110] flex flex-col p-6 rounded-r-[2.5rem]">
                <div class="flex justify-between items-start mb-8">
                    <h1 class="text-3xl font-black text-orange-600 tracking-tighter italic uppercase">
                        Movvia
                    </h1>

                    <button @click="sideMenuOpen = false" class="text-gray-300 text-2xl font-bold">
                        ✕
                    </button>
                </div>

                {{-- PERFIL --}}
                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100">
                    <div
                        class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white text-2xl font-black italic shadow-lg shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-1">
                            {{ auth()->user()->role === \App\Enums\UserRole::Passenger ? 'Passageiro' : 'Motorista' }}
                        </p>

                        <p class="font-black text-xl text-gray-900 truncate leading-none">
                            {{ auth()->user()->name }}
                        </p>

                        @if(auth()->user()->role === \App\Enums\UserRole::Driver)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-yellow-500 text-sm">⭐</span>
                                <span class="font-black text-sm text-gray-800">4.9</span>
                                <span class="text-xs text-gray-400 font-bold uppercase tracking-widest">Avaliação</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- MENU PASSAGEIRO --}}
                @if(auth()->user()->role === \App\Enums\UserRole::Passenger)
                    <nav class="space-y-2 flex-1">
                        <a href="{{ route('ride.history') }}" wire:navigate
                            class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">📜</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Histórico de Corridas
                            </span>
                        </a>

                        <a href="{{ route('profile.edit') }}" wire:navigate
                            class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">⚙️</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Configurações
                            </span>
                        </a>
                    </nav>

                    <div class="pt-6 border-t border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="w-full flex items-center justify-center gap-3 py-4 bg-gray-50 rounded-2xl text-gray-400 font-black uppercase text-[10px] tracking-widest hover:bg-red-50 hover:text-red-600 transition-all">
                                Sair do Movvia 🚪
                            </button>
                        </form>
                    </div>
                @endif

                {{-- MENU MOTORISTA --}}
                @if(auth()->user()->role === \App\Enums\UserRole::Driver)
                    <nav class="space-y-2 flex-1">
                        <a href="#" class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">🎯</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Preferências de Corrida
                            </span>
                        </a>

                        <a href="#" class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">💰</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Ganhos
                            </span>
                        </a>

                        <a href="{{ route('ride.history') }}" wire:navigate
                            class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">📜</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Histórico de Corridas
                            </span>
                        </a>

                        <a href="{{ route('profile.edit') }}" wire:navigate
                            class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">⚙️</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Configurações
                            </span>
                        </a>

                        <a href="#" class="flex items-center gap-4 p-4 rounded-2xl hover:bg-orange-50 group transition-all">
                            <span class="text-xl">❓</span>
                            <span
                                class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">
                                Ajuda
                            </span>
                        </a>
                    </nav>

                    {{-- RODAPÉ MOTORISTA --}}
                    <div class="pt-6 border-t border-gray-100 space-y-3">
                        <div
                            class="rounded-2xl bg-gradient-to-r from-orange-500 via-orange-500 to-blue-700 text-white p-4 shadow-[0_15px_30px_-10px_rgba(249,115,22,0.35)]">
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-2">
                                Status de trabalho
                            </p>

                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-black text-lg leading-none">
                                        Ativar corridas
                                    </p>
                                    <p class="text-xs font-bold text-white/80 mt-1">
                                        Fique online para receber chamadas
                                    </p>
                                </div>

                                <div class="shrink-0">
                                    <livewire:driver-status-toggle />
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="w-full flex items-center justify-center gap-3 py-4 bg-gray-50 rounded-2xl text-gray-400 font-black uppercase text-[10px] tracking-widest hover:bg-red-50 hover:text-red-600 transition-all">
                                Sair do Movvia 🚪
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================= --}}
        {{-- ÁREA PRINCIPAL --}}
        {{-- ========================= --}}
        <main class="flex-1 relative w-full h-full overflow-hidden">

            @if(auth()->user()->role === \App\Enums\UserRole::Passenger)

                {{-- ========================= --}}
                {{-- VISÃO DO PASSAGEIRO --}}
                {{-- ========================= --}}
                <div class="absolute inset-0 w-full h-full">

                    {{-- MAPA --}}
                    <div class="absolute inset-0 z-0 bg-gray-200" wire:ignore>
                        <div id="map" class="w-full h-full"></div>
                    </div>

                    {{-- STATUS / AVALIAÇÃO --}}
                    <div class="absolute inset-0 pointer-events-none z-[200]">
                        <livewire:passenger-ride-status />
                    </div>

                    {{-- BOTTOM SHEET PASSAGEIRO --}}
                    <div
                        class="absolute inset-x-0 bottom-0 z-[140] pointer-events-none p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
                        <div class="max-w-md mx-auto pointer-events-auto">
                            <livewire:request-ride />
                        </div>
                    </div>
                </div>

            @elseif(auth()->user()->role === \App\Enums\UserRole::Driver)

                        {{-- ========================= --}}
                        {{-- VISÃO DO MOTORISTA --}}
                        {{-- ========================= --}}
                        <div class="absolute inset-0 w-full h-full overflow-hidden">

                            {{-- MAPA FULLSCREEN --}}
                            <div class="absolute inset-0 z-0 bg-gray-200" wire:ignore>
                                <div id="driver-map" class="w-full h-full"></div>
                            </div>

                            {{-- SOBREPOSIÇÃO SUAVE --}}
                            <div
                                class="absolute inset-0 bg-gradient-to-b from-black/5 via-transparent to-black/10 pointer-events-none z-[5]">
                            </div>

                            {{-- TOP PILL DE GANHOS --}}
                            <<div class="absolute top-4 inset-x-0 z-[120] pointer-events-none px-4">
                                <div class="max-w-md mx-auto flex justify-center">
                                    <div
                                        class="pointer-events-auto bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-full px-5 py-3 shadow-[0_15px_35px_-10px_rgba(37,99,235,0.45)] border border-blue-400/20 backdrop-blur-xl flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center border border-white/10 shadow-inner">
                                            <span class="text-xl">🪙</span>
                                        </div>

                                        <div class="leading-none">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-orange-300 mb-1">
                                                Ganhos do dia
                                            </p>
                                            <p class="text-2xl font-black tracking-tight">
                                                R$ {{ number_format(auth()->user()->daily_earnings ?? 0, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                        </div>



                        {{-- ESTADO / CHAMADAS DO MOTORISTA --}}
                        <div class="absolute inset-0 z-[140] pointer-events-none">
                            <div class="pointer-events-auto">
                                <livewire:available-rides />
                            </div>
                        </div>

                        {{-- BOTTOM SHEET ESTILO APP --}}
                        <div class="absolute bottom-0 inset-x-0 z-[145] pointer-events-none">
                            <div class="max-w-md mx-auto pointer-events-auto">
                                <div
                                    class="bg-white/96 backdrop-blur-2xl rounded-t-[2rem] shadow-[0_-20px_40px_-20px_rgba(0,0,0,0.35)] border-t border-white/70 px-6 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))]">

                                    {{-- HANDLE --}}
                                    <div class="flex justify-center mb-4">
                                        <div class="w-12 h-1.5 rounded-full bg-gray-300"></div>
                                    </div>

                                    {{-- CABEÇALHO DO SHEET --}}
                                    <div class="flex items-center justify-center mb-5">
                                        <div class="text-center">
                                            <p class="text-3xl font-black tracking-tight transition-all duration-300"
                                                :class="driverOnline ? 'text-blue-700' : 'text-gray-900'"
                                                x-text="driverOnline ? 'Procurando viagens' : 'Offline'"></p>

                                            <p class="text-[11px] font-black uppercase tracking-widest mt-1 transition-all duration-300"
                                                :class="driverOnline ? 'text-orange-500' : 'text-gray-400'"
                                                x-text="driverOnline ? 'Movvia ativo no mapa' : 'Motorista indisponível'"></p>
                                        </div>
                                    </div>

                                    {{-- ÁREA DINÂMICA --}}
                                    <div class="space-y-4">
                                        <livewire:active-trip />
                                    </div>
                                </div>
                            </div>
                        </div>

                </div>
            @endif

    </main>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
        <style>
            .leaflet-routing-container {
                display: none !important;
            }

            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
        <script>
            function initMaps() {
                // MAPA PASSAGEIRO
                const pMapEl = document.getElementById('map');
                if (pMapEl) {
                    if (window.rideMap) window.rideMap.remove();

                    window.rideMap = L.map('map', { zoomControl: false }).setView([-12.6975, -38.3242], 14);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(window.rideMap);

                    setTimeout(() => window.rideMap.invalidateSize(), 400);
                }

                // MAPA MOTORISTA
                const dMapEl = document.getElementById('driver-map');
                if (dMapEl) {
                    if (window.dMap) window.dMap.remove();

                    window.dMap = L.map('driver-map', { zoomControl: false }).setView([-12.6975, -38.3242], 14);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(window.dMap);

                    setTimeout(() => window.dMap.invalidateSize(), 400);
                }
            }

            document.addEventListener('DOMContentLoaded', initMaps);
            document.addEventListener('livewire:navigated', initMaps);
        </script>
    @endpush
</x-app-layout>