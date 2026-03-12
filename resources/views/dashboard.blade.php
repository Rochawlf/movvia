<x-app-layout>
    <div class="h-screen bg-[#121212] flex flex-col overflow-hidden"
        x-data="{ mapMode: 'default', sideMenuOpen: false }" @map-mode-changed.window="mapMode = $event.detail">

        {{-- ========================= --}}
        {{-- MENU LATERAL PASSAGEIRO --}}
        {{-- ========================= --}}
        <div class="z-[220] absolute top-0 left-0">

            {{-- BOTÃO HAMBÚRGUER --}}
            <button x-show="mapMode !== 'mapSelection'" x-transition:enter="transition ease-out duration-300 delay-150"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                @click="sideMenuOpen = true"
                class="absolute top-6 left-4 w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-2xl border border-white/10 shadow-[0_15px_35px_-12px_rgba(0,0,0,0.45)] flex flex-col items-center justify-center gap-1.5 z-50 hover:bg-white/15 transition-all active:scale-95">
                <div class="w-6 h-0.5 bg-white rounded-full"></div>
                <div class="w-6 h-0.5 bg-white rounded-full"></div>
                <div class="w-4 h-0.5 bg-white rounded-full self-start ml-3"></div>
            </button>

            {{-- BACKDROP MENU --}}
            <div x-show="sideMenuOpen" x-cloak @click="sideMenuOpen = false"
                class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100]"></div>

            {{-- DRAWER MENU --}}
            <div x-show="sideMenuOpen" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed top-0 left-0 bottom-0 w-[86%] max-w-[340px] bg-[#171717]/95 backdrop-blur-2xl shadow-2xl z-[110] flex flex-col p-6 rounded-r-[2.5rem] border-r border-white/10">
                <div class="flex justify-between items-start mb-8">
                    <h1
                        class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-blue-600 tracking-tighter italic uppercase">
                        Movvia
                    </h1>

                    <button @click="sideMenuOpen = false"
                        class="text-white/40 text-2xl font-bold hover:text-white transition">
                        ✕
                    </button>
                </div>

                {{-- PERFIL --}}
                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-white/10">
                    <div class="relative shrink-0">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-500 to-blue-600 p-[2px] shadow-[0_12px_30px_-10px_rgba(37,99,235,0.35)]">
                            <div
                                class="w-full h-full rounded-2xl bg-[#101010] flex items-center justify-center text-white text-2xl font-black italic">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-black text-orange-400 uppercase tracking-widest mb-1">
                            Passageiro
                        </p>

                        <p class="font-black text-xl text-white truncate leading-none">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-white/50 font-bold mt-2 uppercase tracking-widest">
                            Conta ativa
                        </p>
                    </div>
                </div>

                {{-- MENU PASSAGEIRO --}}
                <nav class="space-y-2 flex-1">
                    <a href="{{ route('ride.history') }}" wire:navigate
                        class="flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 group transition-all">
                        <span class="text-xl">📜</span>
                        <span
                            class="font-black text-xs uppercase tracking-widest text-white/70 group-hover:text-orange-300">
                            Histórico
                        </span>
                    </a>

                    <a href="{{ route('profile.edit') }}" wire:navigate
                        class="flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 group transition-all">
                        <span class="text-xl">⚙️</span>
                        <span
                            class="font-black text-xs uppercase tracking-widest text-white/70 group-hover:text-blue-300">
                            Configurações
                        </span>
                    </a>

                    <a href="#" class="flex items-center gap-4 p-4 rounded-2xl hover:bg-white/5 group transition-all">
                        <span class="text-xl">🎁</span>
                        <span
                            class="font-black text-xs uppercase tracking-widest text-white/70 group-hover:text-orange-300">
                            Promoções
                        </span>
                    </a>
                </nav>

                <div class="pt-6 border-t border-white/10">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="w-full flex items-center justify-center gap-3 py-4 bg-white/5 rounded-2xl text-white/50 font-black uppercase text-[10px] tracking-widest hover:bg-red-500/10 hover:text-red-300 transition-all">
                            Sair do Movvia 🚪
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- HEADER FLUTUANTE PASSAGEIRO --}}
        {{-- ========================= --}}
        <div class="absolute top-5 inset-x-0 z-[170] px-4 pointer-events-none">
            <div class="max-w-md mx-auto flex items-start justify-between gap-3">

                {{-- PERFIL / SAUDAÇÃO --}}
                <div
                    class="pointer-events-auto flex items-center gap-3 bg-black/25 backdrop-blur-2xl border border-white/10 rounded-[1.75rem] px-3 py-3 shadow-[0_18px_45px_-18px_rgba(0,0,0,0.6)]">
                    <div class="relative shrink-0">
                        <div
                            class="absolute inset-0 rounded-full bg-gradient-to-r from-orange-500 to-blue-600 blur-sm opacity-60">
                        </div>
                        <div
                            class="relative w-12 h-12 rounded-full bg-gradient-to-br from-orange-500 to-blue-600 p-[2px]">
                            <div
                                class="w-full h-full rounded-full bg-[#121212] flex items-center justify-center text-white text-lg font-black">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-300">
                            Movvia
                        </p>
                        <p class="text-white text-sm font-black truncate">
                            Olá, {{ explode(' ', auth()->user()->name)[0] }}!
                        </p>
                    </div>
                </div>

                {{-- BOTÃO SEGURANÇA / ALERTAS --}}
                <button
                    class="pointer-events-auto w-12 h-12 rounded-2xl bg-black/25 backdrop-blur-2xl border border-white/10 shadow-[0_18px_45px_-18px_rgba(0,0,0,0.6)] flex items-center justify-center text-white text-xl hover:bg-white/10 transition-all active:scale-95">
                    🛡️
                </button>
            </div>
        </div>

        {{-- ========================= --}}
        {{-- ÁREA PRINCIPAL PASSAGEIRO --}}
        {{-- ========================= --}}
        <main class="flex-1 relative w-full h-full overflow-hidden">

            <div class="absolute inset-0 w-full h-full overflow-hidden">

                {{-- MAPA --}}
                <div class="absolute inset-0 z-0 bg-[#121212]" wire:ignore>
                    <div id="map" class="w-full h-full"></div>
                </div>

                {{-- OVERLAY GRADIENTE PARA DAR PROFUNDIDADE --}}
                <div
                    class="absolute inset-0 z-[1] pointer-events-none bg-gradient-to-b from-black/30 via-transparent to-black/40">
                </div>

                {{-- STATUS / AVALIAÇÃO --}}
                <div class="absolute inset-0 z-[210] pointer-events-none">
                    <livewire:passenger-ride-status />
                </div>

                {{-- BOTTOM SHEET MODERNA --}}
                <div
                    class="absolute inset-x-0 bottom-0 z-[150] pointer-events-none px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <div class="max-w-md mx-auto pointer-events-auto">

                        {{-- CASCA VISUAL DA BOTTOM SHEET --}}
                        <div class="relative rounded-t-[2.25rem] overflow-hidden">
                            <div
                                class="absolute inset-0 bg-[#161616]/92 backdrop-blur-3xl border border-white/10 shadow-[0_-20px_50px_-18px_rgba(0,0,0,0.7)]">
                            </div>

                            <div class="relative px-4 pt-3">
                                {{-- HANDLE --}}
                                <div class="flex justify-center mb-4">
                                    <div class="w-12 h-1.5 rounded-full bg-white/20"></div>
                                </div>

                                {{-- TABS RÁPIDAS --}}
                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    <button
                                        class="h-11 rounded-2xl bg-gradient-to-r from-orange-500/20 to-blue-600/20 text-white text-[11px] font-black uppercase tracking-widest border border-orange-400/10 hover:from-orange-500/30 hover:to-blue-600/30 transition-all">
                                        Viagem
                                    </button>

                                    <button
                                        class="h-11 rounded-2xl bg-white/5 text-white/70 text-[11px] font-black uppercase tracking-widest border border-white/5 hover:bg-white/10 transition-all">
                                        Entregas
                                    </button>

                                    <button
                                        class="h-11 rounded-2xl bg-white/5 text-white/70 text-[11px] font-black uppercase tracking-widest border border-white/5 hover:bg-white/10 transition-all">
                                        Histórico
                                    </button>
                                </div>

                                {{-- FAVORITOS RÁPIDOS --}}
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <button
                                        class="rounded-[1.4rem] bg-gradient-to-r from-orange-500/20 to-blue-600/20 border border-orange-400/15 px-4 py-4 text-left hover:from-orange-500/30 hover:to-blue-600/30 transition-all">
                                        <p
                                            class="text-[10px] font-black uppercase tracking-widest text-orange-300 mb-1">
                                            Favorito</p>
                                        <p class="text-white font-black text-sm">🏠 Casa</p>
                                    </button>

                                    <button
                                        class="rounded-[1.4rem] bg-white/5 border border-white/8 px-4 py-4 text-left hover:bg-white/8 transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-300 mb-1">
                                            Favorito</p>
                                        <p class="text-white font-black text-sm">💼 Trabalho</p>
                                    </button>
                                </div>

                                {{-- REQUEST RIDE ATUAL DENTRO DA NOVA CASCA --}}
                                <div class="pb-2">
                                    <livewire:request-ride />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

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

            .leaflet-container {
                background: #121212;
            }

            .leaflet-control-attribution,
            .leaflet-control-zoom {
                display: none !important;
            }

            .leaflet-tile {
                filter: brightness(0.42) contrast(1.15) saturate(0.75) hue-rotate(190deg);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
        <script>
            function initPassengerMap() {
                const pMapEl = document.getElementById('map');

                if (pMapEl) {
                    if (window.rideMap) window.rideMap.remove();

                    window.rideMap = L.map('map', {
                        zoomControl: false,
                        attributionControl: false
                    }).setView([-12.6975, -38.3242], 14);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(window.rideMap);

                    setTimeout(() => window.rideMap.invalidateSize(), 400);
                }
            }

            document.addEventListener('DOMContentLoaded', initPassengerMap);
            document.addEventListener('livewire:navigated', initPassengerMap);
        </script>
    @endpush
</x-app-layout>