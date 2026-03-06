<x-app-layout>
<div class="h-screen bg-gray-50 flex flex-col overflow-hidden" x-data="{ mapMode: 'default' }" @map-mode-changed.window="mapMode = $event.detail">

    {{-- HAMBURGER MENU & DRAWER --}}
    <div x-data="{ sideMenuOpen: false }" class="z-[150] absolute top-0 left-0">
        
        {{-- BOTÃO HAMBURGUER --}}
        <button
            x-show="mapMode !== 'mapSelection'"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-transition:enter="transition ease-out duration-300 delay-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            @click="sideMenuOpen = true"
            class="absolute top-4 left-4 w-11 h-11 bg-white/90 backdrop-blur-xl rounded-2xl shadow-lg border border-white/60 flex flex-col items-center justify-center gap-1 z-50 hover:bg-white transition-all duration-300 active:scale-95"
        >
            <div class="w-5 h-0.5 bg-gray-800 rounded-full"></div>
            <div class="w-5 h-0.5 bg-gray-800 rounded-full"></div>
            <div class="w-5 h-0.5 bg-gray-800 rounded-full"></div>
        </button>

        {{-- BACKDROP --}}
        <div
            x-show="sideMenuOpen"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sideMenuOpen = false"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[100]"
        ></div>

        {{-- DRAWER --}}
        <div
            x-show="sideMenuOpen"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed top-0 left-0 bottom-0 w-[82%] max-w-[300px] bg-white/95 backdrop-blur-2xl shadow-2xl z-[110] flex flex-col p-6 rounded-r-[2rem] border-r border-white/60"
        >
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600 tracking-tighter italic">
                    MOVVIA
                </h1>

                <button
                    @click="sideMenuOpen = false"
                    class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-all text-lg font-bold"
                >
                    ✕
                </button>
            </div>

            <div class="flex items-center gap-3 mb-8 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-md text-white text-xl font-black italic">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Passageiro</p>
                    <p class="font-black text-sm text-gray-900 truncate max-w-[170px]">{{ auth()->user()->name }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-2 flex-1">
                <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 py-3.5 px-4 rounded-2xl hover:bg-orange-50 group transition-all duration-300">
                    <span class="text-xl grayscale group-hover:grayscale-0 transition-all">⚙️</span>
                    <span class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">Configurações</span>
                </a>

                <a href="{{ route('ride.history') }}" wire:navigate class="flex items-center gap-3 py-3.5 px-4 rounded-2xl hover:bg-orange-50 group transition-all duration-300">
                    <span class="text-xl grayscale group-hover:grayscale-0 transition-all">📜</span>
                    <span class="font-black text-xs uppercase tracking-widest text-gray-600 group-hover:text-orange-600">Histórico</span>
                </a>
            </div>

            <div class="mt-auto pt-5 border-t border-gray-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-50 rounded-2xl hover:bg-red-50 text-gray-500 hover:text-red-600 transition-all duration-300 font-black uppercase tracking-widest text-[11px]">
                        Sair do App
                        <span class="text-base">🚪</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- MAIN --}}
    <main class="flex-1 relative w-full h-full overflow-hidden">

        {{-- STATUS FLUTUANTE --}}
        <div class="absolute inset-0 pointer-events-none z-[100]">
            <livewire:passenger-ride-status />
        </div>

        {{-- PASSAGEIRO --}}
        @if(auth()->user()->role === \App\Enums\UserRole::Passenger)

        <div class="absolute inset-0 w-full h-full relative">

            {{-- MAPA --}}
            <div class="absolute inset-0 z-0 bg-gray-200">
                <div id="map" class="w-full h-full" wire:ignore></div>
            </div>

            {{-- PAINEL PASSAGEIRO --}}
            <div class="absolute inset-x-0 bottom-0 z-40 pointer-events-none">
                <div class="w-full max-w-md mx-auto px-3 pb-3 sm:px-4 sm:pb-4">
                    <div class="pointer-events-auto">
                        <livewire:request-ride />
                    </div>
                </div>
            </div>

        </div>

        {{-- MOTORISTA --}}
        @elseif(auth()->user()->role === \App\Enums\UserRole::Driver)

        <div class="w-full h-full relative">

            {{-- MAPA --}}
            <div class="absolute inset-0 z-0">
                <div id="driver-map" class="w-full h-full" wire:ignore></div>
            </div>

            {{-- PAINEL MOTORISTA --}}
            <div class="absolute inset-x-0 bottom-0 lg:inset-x-auto lg:bottom-auto lg:top-4 lg:right-4 z-40 pointer-events-none">
                <div class="w-full max-w-md mx-auto lg:mx-0 px-3 pb-3 lg:px-0 lg:pb-0">
                    <div class="bg-white rounded-t-[1.75rem] lg:rounded-[2rem] shadow-xl p-4 lg:p-5 flex flex-col gap-4 overflow-y-auto max-h-[46vh] lg:max-h-[calc(100vh-2rem)] no-scrollbar pointer-events-auto">

                        <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl shrink-0 border border-gray-100">
                            <span class="text-[11px] font-black uppercase tracking-widest text-gray-500">
                                Trabalho
                            </span>
                            <livewire:driver-status-toggle />
                        </div>

                        <div class="grid grid-cols-2 gap-3 shrink-0">
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Ganhos</p>
                                <p class="text-xl font-black text-gray-900 mt-1">R$ 127</p>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Nota</p>
                                <p class="text-xl font-black text-orange-500 mt-1">4.9</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <livewire:active-trip />
                            <livewire:available-rides />
                        </div>

                    </div>
                </div>
            </div>

        </div>
        @endif

    </main>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:navigated', function () {

    if (document.getElementById('map')) {
        setTimeout(() => {
            if (window.rideMap) window.rideMap.remove();

            window.rideMap = L.map('map', { zoomControl: false }).setView([-12.6975, -38.3242], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.rideMap);

            setTimeout(() => {
                window.rideMap.invalidateSize();
            }, 300);
        }, 200);
    }

    if (document.getElementById('driver-map')) {
        setTimeout(() => {
            if (window.dMap) window.dMap.remove();

            window.dMap = L.map('driver-map', { zoomControl: false }).setView([-12.6975, -38.3242], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.dMap);

            setTimeout(() => {
                window.dMap.invalidateSize();
            }, 300);
        }, 200);
    }

});
</script>
@endpush
</x-app-layout>