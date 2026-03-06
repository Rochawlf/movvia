<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $origin = '';
    public string $destination = '';
    public string $category = 'car';

    public $originLat = null;
    public $originLng = null;
    public $destinationLat = null;
    public $destinationLng = null;

    public $distance = 0;
    public $fare = 0;

    public function updated($property)
    {
        if (in_array($property, ['distance', 'category'])) {
            $this->calculateFare();
        }
    }

    public function calculateFare()
    {
        $distance = (float) $this->distance;

        if ($distance <= 0) {
            $this->fare = 0;
            return;
        }

        if (in_array($this->category, ['moto', 'delivery'])) {
            $basePrice = 5.20;
            $extraRate = 0.75;
            $limit = 2.7;
        } else {
            $basePrice = 8.00;
            $extraRate = 1.30;
            $limit = 2.5;
        }

        $total = $distance <= $limit
            ? $basePrice
            : $basePrice + (($distance - $limit) * $extraRate);

        $this->fare = round($total, 2);
    }

    public function requestRide()
    {
        $this->validate([
            'originLat' => 'required|numeric',
            'originLng' => 'required|numeric',
            'destinationLat' => 'required|numeric',
            'destinationLng' => 'required|numeric',
            'distance' => 'required|numeric|min:0.1',
        ], [
            'originLat.required' => 'Defina o ponto de partida.',
            'destinationLat.required' => 'Defina o destino.',
            'distance.min' => 'A distância é muito curta.',
        ]);

        Ride::create([
            'passenger_id' => Auth::id(),
            'origin_address' => $this->origin,
            'destination_address' => $this->destination,
            'origin_lat' => $this->originLat,
            'origin_lng' => $this->originLng,
            'destination_lat' => $this->destinationLat,
            'destination_lng' => $this->destinationLng,
            'category' => $this->category,
            'status' => RideStatus::Pending,
            'fare' => $this->fare,
            'distance' => $this->distance,
        ]);

        session()->flash('message', '🚗 Movvia solicitado com sucesso!');

        $this->reset([
            'destination',
            'destinationLat',
            'destinationLng',
            'distance',
            'fare',
        ]);
    }
};

?>

<div
    id="ride-request-root"
    class="w-full relative"
    x-data="{
        mode: 'default',
        selectionTarget: 'destination',
        setMode(m) {
            this.mode = m;
        },
        openMapSelection(target) {
            this.selectionTarget = target;
            this.mode = 'mapSelection';
            window.dispatchEvent(new CustomEvent('movvia-select-target', {
                detail: { target }
            }));
        }
    }"
    x-on:route-calculated.window="setMode('default')"
>
    {{-- BOTÃO VOLTAR --}}
    <button
        type="button"
        @click="setMode('search')"
        :class="{
            'opacity-100 pointer-events-auto scale-100': mode === 'mapSelection',
            'opacity-0 pointer-events-none scale-90': mode !== 'mapSelection'
        }"
        class="fixed top-[max(1rem,env(safe-area-inset-top))] left-4 z-[160] bg-white text-gray-900 w-12 h-12 rounded-full shadow-[0_5px_15px_rgba(0,0,0,0.2)] flex items-center justify-center font-bold text-xl active:scale-95 transition-all duration-300"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </button>

    {{-- PINO CENTRAL --}}
    <div
        :class="{ 'opacity-100': mode === 'mapSelection', 'opacity-0 hidden': mode !== 'mapSelection' }"
        class="fixed inset-0 z-[100] pointer-events-none flex items-center justify-center pb-24 transition-opacity duration-300"
    >
        <div class="relative flex flex-col items-center">
            <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center shadow-lg border-4 border-white">
                <div class="w-3 h-3 bg-white rounded-full"></div>
            </div>
            <div class="w-1 h-6 bg-black"></div>
            <div class="w-3 h-1 bg-black/40 rounded-full blur-[1px]"></div>
        </div>
    </div>

    {{-- BOTTOM SHEET --}}
    <div
        class="fixed inset-x-0 bottom-0 bg-[#121212] rounded-t-[2rem] shadow-[0_-10px_40px_rgba(0,0,0,0.4)] z-[160] p-6 pb-[max(2rem,env(safe-area-inset-bottom))] transition-transform duration-300 pointer-events-auto flex flex-col items-center border-t border-gray-800"
        :class="{ 'translate-y-0': mode === 'mapSelection', 'translate-y-full': mode !== 'mapSelection' }"
    >
        <div class="w-12 h-1.5 bg-gray-600 rounded-full mb-6"></div>

        <template x-if="selectionTarget === 'origin'">
            <div class="text-center">
                <h3 class="text-white text-[22px] font-bold tracking-tight">Defina o embarque</h3>
                <p class="text-gray-400 text-sm mt-1 mb-8">Arraste o mapa para escolher onde o motorista vai te buscar</p>
            </div>
        </template>

        <template x-if="selectionTarget === 'destination'">
            <div class="text-center">
                <h3 class="text-white text-[22px] font-bold tracking-tight">Defina o destino</h3>
                <p class="text-gray-400 text-sm mt-1 mb-8">Arraste o mapa para escolher onde você quer chegar</p>
            </div>
        </template>

        <button
            type="button"
            onclick="confirmMapLocation()"
            class="w-full bg-white text-black py-4 rounded-xl font-bold text-[17px] hover:bg-gray-200 active:scale-95 transition-all"
        >
            Confirmar no mapa
        </button>
    </div>

    {{-- CARD PRINCIPAL --}}
    <div
        :class="{
            'opacity-0 pointer-events-none translate-y-8 scale-95': mode === 'mapSelection',
            'opacity-100 pointer-events-auto translate-y-0 scale-100': mode !== 'mapSelection'
        }"
        class="bg-white/90 backdrop-blur-2xl rounded-[2rem] shadow-[0_25px_50px_-15px_rgba(0,0,0,0.35)] border border-white/60 p-5 sm:p-6 space-y-5 relative overflow-hidden transition-all duration-500 transform origin-bottom"
    >
        <div class="absolute inset-0 bg-gradient-to-br from-white/60 to-transparent pointer-events-none"></div>

        <div class="flex items-center justify-between relative z-10 pt-1">
            <h2 class="text-lg sm:text-xl font-black text-gray-900 italic tracking-tighter uppercase flex items-center gap-3">
                <span class="w-2 h-6 bg-gradient-to-b from-orange-400 to-orange-600 rounded-full"></span>
                Para onde <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600">vamos?</span>
            </h2>

            <button
                type="button"
                :class="{ 'hidden': mode !== 'search' }"
                @click="setMode('default')"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 transition-all font-bold"
            >
                ✕
            </button>
        </div>

        @if (session()->has('message'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                class="relative z-10 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl font-bold text-sm"
            >
                {{ session('message') }}
            </div>
        @endif

        <div class="space-y-5 relative z-10">
            <div class="relative pl-10">
                <div class="absolute left-[13px] top-6 bottom-[10px] w-0.5 border-l-2 border-dashed border-gray-300"></div>

                {{-- PARTIDA --}}
                <div class="space-y-2 mb-5">
                    <div class="flex items-center justify-between">
                        <label class="text-[9px] font-black text-blue-500 uppercase tracking-widest ml-1">Sua Partida</label>

                        <button
                            type="button"
                            @click="openMapSelection('origin')"
                            class="text-[10px] font-black uppercase tracking-widest text-blue-600"
                        >
                            escolher no mapa
                        </button>
                    </div>

                    <div class="relative">
                        <div class="absolute -left-[35px] top-1/2 -translate-y-1/2 h-4 w-4 rounded-full bg-blue-500 border-4 border-white shadow-md"></div>

                        <input
                            type="text"
                            wire:model.live.debounce.800ms="origin"
                            placeholder="Sua localização atual"
                            class="w-full bg-blue-50/60 border-2 border-blue-100 rounded-2xl py-3.5 px-4 text-sm font-bold text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner"
                        >
                    </div>
                </div>

                {{-- DESTINO --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[9px] font-black text-orange-500 uppercase tracking-widest ml-1">Destino Final</label>

                        <button
                            type="button"
                            @click="openMapSelection('destination')"
                            class="text-[10px] font-black uppercase tracking-widest text-orange-600"
                        >
                            escolher no mapa
                        </button>
                    </div>

                    <div class="relative">
                        <div class="absolute -left-[35px] top-1/2 -translate-y-1/2 h-4 w-4 rounded-full bg-orange-500 border-4 border-white shadow-[0_0_10px_rgba(249,115,22,0.6)] animate-pulse"></div>

                        <input
                            type="text"
                            wire:model.live.debounce.800ms="destination"
                            @focus="mode = 'search'"
                            placeholder="Busque destino ou selecione no mapa"
                            class="w-full bg-orange-50/60 border-2 border-orange-100 rounded-2xl py-3.5 px-4 text-sm font-bold text-gray-800 placeholder-gray-400 focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-500/10 transition-all shadow-inner"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- MODO BUSCA --}}
        <div x-show="mode === 'search'" x-collapse class="relative z-10">
            <div class="pt-4 border-t border-gray-100 space-y-4">
                <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                    <h3 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">
                        Favoritos Recentes
                    </h3>
                    <livewire:recent-rides :limit="3" />
                </div>

                <button
                    type="button"
                    @click="openMapSelection('destination')"
                    class="w-full flex items-center justify-between p-4 rounded-3xl bg-gray-900 text-white hover:bg-black transition-all active:scale-95"
                >
                    <div class="text-left">
                        <p class="font-black text-sm uppercase tracking-wider">Escolher destino no mapa</p>
                        <p class="text-[9px] font-medium text-gray-400 uppercase tracking-widest">Localização exata</p>
                    </div>
                    <span class="text-xl">🗺️</span>
                </button>
            </div>
        </div>

        {{-- MODO PADRÃO --}}
        <div x-show="mode === 'default'" x-collapse class="relative z-10 space-y-5">
            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-50">
                <button
                    type="button"
                    wire:click="$set('category', 'car')"
                    class="flex flex-col items-center justify-center gap-1 p-4 rounded-[1.25rem] border-2 transition-all duration-300 active:scale-95 {{ $category === 'car' ? 'border-orange-500 bg-orange-50/80 shadow-[0_10px_20px_-10px_rgba(249,115,22,0.5)]' : 'border-gray-100 bg-white/50 hover:bg-gray-50 hover:shadow-sm' }}"
                >
                    <span class="text-3xl">🚗</span>
                    <div class="text-center">
                        <p class="text-[10px] font-black uppercase tracking-widest {{ $category === 'car' ? 'text-orange-600' : 'text-gray-500' }}">Carro</p>
                        <p class="text-[8px] font-bold mt-1 {{ $category === 'car' ? 'text-orange-400' : 'text-gray-400' }}">A partir R$ 8</p>
                    </div>
                </button>

                <button
                    type="button"
                    wire:click="$set('category', 'moto')"
                    class="flex flex-col items-center justify-center gap-1 p-4 rounded-[1.25rem] border-2 transition-all duration-300 active:scale-95 {{ $category === 'moto' ? 'border-orange-500 bg-orange-50/80 shadow-[0_10px_20px_-10px_rgba(249,115,22,0.5)]' : 'border-gray-100 bg-white/50 hover:bg-gray-50 hover:shadow-sm' }}"
                >
                    <span class="text-3xl">🏍️</span>
                    <div class="text-center">
                        <p class="text-[10px] font-black uppercase tracking-widest {{ $category === 'moto' ? 'text-orange-600' : 'text-gray-500' }}">Moto</p>
                        <p class="text-[8px] font-bold mt-1 {{ $category === 'moto' ? 'text-orange-400' : 'text-gray-400' }}">A partir R$ 5</p>
                    </div>
                </button>
            </div>

            @if($fare > 0)
                <div class="flex justify-between items-center bg-gray-900 rounded-[1.25rem] p-4 text-white border border-gray-800">
                    <div>
                        <p class="text-[9px] font-black text-orange-400 uppercase tracking-widest mb-1 opacity-80">Estimativa</p>
                        <p class="text-xl font-black tracking-tighter">R$ {{ number_format($fare, 2, ',', '.') }}</p>
                    </div>
                    <div class="text-right flex flex-col items-end gap-1">
                        <p class="text-[9px] font-bold text-gray-300 uppercase">📏 {{ number_format($distance, 1, ',', '.') }} km</p>
                        <p class="text-[9px] font-bold text-gray-300 uppercase">⏱️ ~{{ ceil($distance * 3) }} min</p>
                    </div>
                </div>
            @endif

            <button
                type="button"
                wire:click="requestRide"
                @disabled(!$originLat || !$originLng || !$destinationLat || !$destinationLng || $fare <= 0)
                class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-4 rounded-[1.25rem] font-black uppercase tracking-widest text-sm shadow-[0_15px_30px_-10px_rgba(249,115,22,0.5)] active:scale-95 transition-all duration-300 disabled:opacity-50 disabled:grayscale disabled:shadow-none"
            >
                Pedir Movvia
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    if (window.movviaRideRequestInitialized) return;
    window.movviaRideRequestInitialized = true;

    let routingControl = null;
    let originMarker = null;
    let destinationMarker = null;
    let liveLocationMarker = null;
    let watchId = null;
    let currentSelectionTarget = 'destination';
    let typingTimerOrigin = null;
    let typingTimerDestination = null;

    let state = {
        origin: null,
        destination: null
    };

    const doneTypingInterval = 800;

    const originIcon = L.divIcon({
        className: 'custom-origin-icon',
        html: `
            <div class="w-5 h-5 bg-blue-500 border-[3px] border-white rounded-full shadow-[0_0_10px_rgba(59,130,246,0.6)] relative flex items-center justify-center">
                <div class="absolute inset-0 rounded-full bg-blue-500 animate-[ping_2s_cubic-bezier(0,0,0.2,1)_infinite] opacity-40"></div>
            </div>
        `,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    const destinationIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const liveIcon = L.divIcon({
        className: 'custom-live-icon',
        html: `
            <div class="relative">
                <div class="w-5 h-5 rounded-full bg-green-500 border-4 border-white shadow-lg"></div>
                <div class="absolute inset-0 rounded-full bg-green-400 opacity-30 animate-ping"></div>
            </div>
        `,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    function getComponent() {
        const root = document.getElementById('ride-request-root');
        if (!root) return null;

        const wireId = root.getAttribute('wire:id');
        if (!wireId || !window.Livewire) return null;

        return Livewire.find(wireId);
    }

    function waitForMap(callback, attempts = 0) {
        if (window.rideMap && typeof window.rideMap.setView === 'function') {
            callback(window.rideMap);
            return;
        }

        if (attempts > 40) return;
        setTimeout(() => waitForMap(callback, attempts + 1), 250);
    }

    async function reverseGeocode(lat, lng) {
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
            const data = await res.json();

            if (!data) return 'Localização selecionada';

            let addr = data.address?.road || data.display_name?.split(',')[0] || 'Localização selecionada';

            if (data.address?.house_number) {
                addr += `, ${data.address.house_number}`;
            }

            return addr;
        } catch (e) {
            return 'Localização selecionada';
        }
    }

    async function searchAddress(query) {
        if (!query || query.length < 4) return null;

        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&countrycodes=br`);
            const data = await res.json();
            return data && data.length ? data[0] : null;
        } catch (e) {
            return null;
        }
    }

    function clearRoute(map) {
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }
    }

    function updateFareAndDistance(distanceKm) {
        const component = getComponent();
        if (!component) return;

        component.set('distance', Number(distanceKm.toFixed(2)));
    }

    function drawRoute(map) {
        if (!state.origin || !state.destination) return;

        clearRoute(map);

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(state.origin.lat, state.origin.lng),
                L.latLng(state.destination.lat, state.destination.lng)
            ],
            lineOptions: {
                styles: [{ color: '#f97316', weight: 7, opacity: 0.85 }]
            },
            createMarker: () => null,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: false
        }).addTo(map);

        routingControl.on('routesfound', function (ev) {
            const route = ev.routes[0];
            if (route?.summary?.totalDistance) {
                updateFareAndDistance(route.summary.totalDistance / 1000);
            }
        });
    }

    async function setOrigin(map, lat, lng, shouldCenter = true, updateText = true) {
        const component = getComponent();
        if (!component) return;

        state.origin = { lat: parseFloat(lat), lng: parseFloat(lng) };

        component.set('originLat', state.origin.lat);
        component.set('originLng', state.origin.lng);

        if (updateText) {
            const address = await reverseGeocode(state.origin.lat, state.origin.lng);
            component.set('origin', address);
        }

        if (originMarker) {
            originMarker.setLatLng([state.origin.lat, state.origin.lng]);
        } else {
            originMarker = L.marker([state.origin.lat, state.origin.lng], { icon: originIcon }).addTo(map);
        }

        if (shouldCenter) {
            map.setView([state.origin.lat, state.origin.lng], 16);
        }

        drawRoute(map);
    }

    async function setDestination(map, lat, lng, shouldCenter = true) {
        const component = getComponent();
        if (!component) return;

        state.destination = { lat: parseFloat(lat), lng: parseFloat(lng) };

        component.set('destinationLat', state.destination.lat);
        component.set('destinationLng', state.destination.lng);

        const address = await reverseGeocode(state.destination.lat, state.destination.lng);
        component.set('destination', address);

        if (destinationMarker) {
            destinationMarker.setLatLng([state.destination.lat, state.destination.lng]);
        } else {
            destinationMarker = L.marker([state.destination.lat, state.destination.lng], { icon: destinationIcon }).addTo(map);
        }

        if (shouldCenter) {
            map.setView([state.destination.lat, state.destination.lng], 16);
        }

        drawRoute(map);
    }

    function bindInputs(map) {
        const originInput = document.querySelector('input[wire\\:model\\.live\\.debounce\\.800ms="origin"]');
        const destinationInput = document.querySelector('input[wire\\:model\\.live\\.debounce\\.800ms="destination"]');

        if (originInput && !originInput.dataset.movviaBound) {
            originInput.dataset.movviaBound = '1';

            originInput.addEventListener('input', function () {
                clearTimeout(typingTimerOrigin);
                typingTimerOrigin = setTimeout(async () => {
                    const result = await searchAddress(this.value);
                    if (!result) return;

                    await setOrigin(map, result.lat, result.lon, true, false);
                    const component = getComponent();
                    if (component) component.set('origin', this.value);
                }, doneTypingInterval);
            });
        }

        if (destinationInput && !destinationInput.dataset.movviaBound) {
            destinationInput.dataset.movviaBound = '1';

            destinationInput.addEventListener('input', function () {
                clearTimeout(typingTimerDestination);
                typingTimerDestination = setTimeout(async () => {
                    const result = await searchAddress(this.value);
                    if (!result) return;

                    await setDestination(map, result.lat, result.lon, true);
                }, doneTypingInterval);
            });
        }
    }

    function startLiveLocation(map) {
        if (!navigator.geolocation) return;
        if (watchId !== null) return;

        watchId = navigator.geolocation.watchPosition(
            async function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                if (liveLocationMarker) {
                    liveLocationMarker.setLatLng([lat, lng]);
                } else {
                    liveLocationMarker = L.marker([lat, lng], { icon: liveIcon }).addTo(map);
                }

                if (!state.origin) {
                    await setOrigin(map, lat, lng, true, true);
                }
            },
            function (error) {
                console.error('Erro ao obter localização em tempo real:', error);
            },
            {
                enableHighAccuracy: true,
                maximumAge: 5000,
                timeout: 15000
            }
        );
    }

    window.confirmMapLocation = function () {
        waitForMap(async function (map) {
            const center = map.getCenter();

            if (currentSelectionTarget === 'origin') {
                await setOrigin(map, center.lat, center.lng, false, true);
            } else {
                await setDestination(map, center.lat, center.lng, false);
            }

            window.dispatchEvent(new CustomEvent('route-calculated'));
        });
    };

    window.addEventListener('movvia-select-target', function (event) {
        currentSelectionTarget = event.detail?.target || 'destination';
    });

    window.addEventListener('ride-selected-from-history', function (event) {
        waitForMap(async function (map) {
            const payload = event.detail?.[0] || event.detail || {};
            const lat = parseFloat(payload.lat);
            const lng = parseFloat(payload.lng);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            await setDestination(map, lat, lng, true);
            window.dispatchEvent(new CustomEvent('route-calculated'));
        });
    });

    function init() {
        waitForMap(function (map) {
            bindInputs(map);
            startLiveLocation(map);
        });
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:initialized', init);
})();
</script>
@endpush