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

    public function getActiveRideProperty()
    {
        return Ride::where('passenger_id', Auth::id())
            ->whereIn('status', [
                RideStatus::Pending,
                RideStatus::Accepted,
                RideStatus::InProgress,
                RideStatus::Finished,
            ])
            ->latest()
            ->first();
    }

    public function getCanChooseCategoryProperty()
    {
        return !$this->activeRide
            && !empty($this->originLat)
            && !empty($this->originLng)
            && !empty($this->destinationLat)
            && !empty($this->destinationLng)
            && (float) $this->distance > 0;
    }

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
    }

    public function cancelRide()
    {
        $ride = $this->activeRide;

        if (!$ride) {
            return;
        }

        if (!in_array($ride->status->value, ['pending', 'accepted'])) {
            return;
        }

        $ride->update([
            'status' => RideStatus::Cancelled,
        ]);

        $this->reset([
            'origin',
            'destination',
            'originLat',
            'originLng',
            'destinationLat',
            'destinationLng',
            'distance',
            'fare',
        ]);

        $this->category = 'car';

        session()->flash('message', 'Corrida cancelada com sucesso.');
        $this->dispatch('ride-cancelled');
    }

    public function clearOrigin()
    {
        $this->origin = '';
        $this->originLat = null;
        $this->originLng = null;

        $this->destination = '';
        $this->destinationLat = null;
        $this->destinationLng = null;

        $this->distance = 0;
        $this->fare = 0;
        $this->category = 'car';

        $this->dispatch('origin-cleared');
    }

    public function clearDestination()
    {
        $this->destination = '';
        $this->destinationLat = null;
        $this->destinationLng = null;

        $this->distance = 0;
        $this->fare = 0;
        $this->category = 'car';

        $this->dispatch('destination-cleared');
    }
};

?>

<div
    id="ride-request-root"
    class="w-full relative"
    wire:poll.5s
    x-data="{
        mode: 'default',
        selectionTarget: 'destination',
        setMode(m) {
            this.mode = m;
            window.dispatchEvent(new CustomEvent('map-mode-changed', { detail: m }));
        },
        openMapSelection(target) {
            this.selectionTarget = target;
            this.mode = 'mapSelection';
            window.dispatchEvent(new CustomEvent('movvia-select-target', {
                detail: { target }
            }));
            window.dispatchEvent(new CustomEvent('map-mode-changed', { detail: 'mapSelection' }));
        }
    }"
    x-on:route-calculated.window="setMode('default')"
    x-on:ride-cancelled.window="setMode('default')"
>
    {{-- BOTÃO VOLTAR --}}
    <button
        type="button"
        @click="setMode('default')"
        :class="{
            'opacity-100 pointer-events-auto scale-100': mode === 'mapSelection',
            'opacity-0 pointer-events-none scale-90': mode !== 'mapSelection'
        }"
        class="fixed top-[max(1rem,env(safe-area-inset-top))] left-4 z-[160] bg-white text-gray-900 w-12 h-12 rounded-full shadow-xl flex items-center justify-center font-bold active:scale-95 transition-all duration-300"
    >
        ✕
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
        </div>
    </div>

    {{-- DRAWER MAPA --}}
    <div
        class="fixed inset-x-0 bottom-0 bg-[#121212] rounded-t-[2rem] shadow-2xl z-[160] p-6 pb-[max(2rem,env(safe-area-inset-bottom))] transition-transform duration-300 pointer-events-auto"
        :class="mode === 'mapSelection' ? 'translate-y-0' : 'translate-y-full'"
    >
        <div class="w-12 h-1.5 bg-gray-600 rounded-full mx-auto mb-6"></div>

        <h3
            class="text-white text-center text-xl font-bold mb-2"
            x-text="selectionTarget === 'origin' ? 'Defina o embarque' : 'Defina o destino'"
        ></h3>

        <p class="text-gray-400 text-sm text-center mb-6">
            Arraste o mapa para a localização exata
        </p>

        <button
            type="button"
            @click="window.confirmMapLocation && window.confirmMapLocation()"
            class="w-full bg-white text-black py-4 rounded-xl font-bold active:scale-95 transition-all"
        >
            Confirmar no mapa
        </button>
    </div>

    {{-- INTERFACE PRINCIPAL --}}
    <div
        x-show="mode !== 'mapSelection'"
        class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl p-6 space-y-5"
    >
        @if (session()->has('message'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl font-bold text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if(!$this->activeRide)
            <h2 class="text-xl font-black italic uppercase">
                Para onde <span class="text-orange-500">vamos?</span>
            </h2>

            <div class="space-y-4 relative">
                <div class="absolute left-3 top-8 bottom-8 w-0.5 border-l-2 border-dashed border-gray-200"></div>

                {{-- ORIGEM --}}
                <div class="relative pl-10">
                    <button
                        type="button"
                        @click="openMapSelection('origin')"
                        class="absolute right-10 text-[10px] font-black text-blue-600 uppercase"
                    >
                        mapa
                    </button>

                    @if($origin)
                        <button
                            type="button"
                            wire:click="clearOrigin"
                            class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 text-sm font-black"
                        >
                            ✕
                        </button>
                    @endif

                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full border-4 border-white"></div>

                    <input
                        type="text"
                        id="input-origin"
                        wire:model.live.debounce.800ms="origin"
                        placeholder="Sua localização"
                        class="w-full bg-gray-50 border-none rounded-2xl py-4 px-4 pr-16 font-bold text-sm"
                    >
                </div>

                {{-- DESTINO --}}
                <div class="relative pl-10">
                    <button
                        type="button"
                        @click="openMapSelection('destination')"
                        class="absolute right-10 text-[10px] font-black text-orange-600 uppercase"
                    >
                        mapa
                    </button>

                    @if($destination)
                        <button
                            type="button"
                            wire:click="clearDestination"
                            class="absolute right-0 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 text-sm font-black"
                        >
                            ✕
                        </button>
                    @endif

                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-orange-500 rounded-full border-4 border-white animate-pulse"></div>

                    <input
                        type="text"
                        id="input-destination"
                        wire:model.live.debounce.800ms="destination"
                        placeholder="Para onde?"
                        class="w-full bg-gray-50 border-none rounded-2xl py-4 px-4 pr-16 font-bold text-sm"
                    >
                </div>
            </div>

            {{-- CATEGORIAS E PREÇO --}}
            @if($this->canChooseCategory)
                <div class="pt-4 border-t space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="$set('category', 'car')"
                            class="p-4 rounded-3xl border-2 transition-all {{ $category === 'car' ? 'border-orange-500 bg-orange-50 shadow-sm' : 'border-gray-200 bg-white' }}"
                        >
                            <span class="text-3xl">🚗</span>
                            <p class="text-[10px] font-black uppercase mt-1 {{ $category === 'car' ? 'text-orange-600' : 'text-gray-400' }}">
                                Carro
                            </p>
                        </button>

                        <button
                            wire:click="$set('category', 'moto')"
                            class="p-4 rounded-3xl border-2 transition-all {{ $category === 'moto' ? 'border-orange-500 bg-orange-50 shadow-sm' : 'border-gray-200 bg-white' }}"
                        >
                            <span class="text-3xl">🏍️</span>
                            <p class="text-[10px] font-black uppercase mt-1 {{ $category === 'moto' ? 'text-orange-600' : 'text-gray-400' }}">
                                Moto
                            </p>
                        </button>
                    </div>

                    <div class="bg-gray-900 rounded-[2rem] p-5 text-white flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-bold text-orange-400 uppercase">Total</p>
                            <p class="text-2xl font-black">R$ {{ number_format($fare, 2, ',', '.') }}</p>
                        </div>

                        <button
                            wire:click="requestRide"
                            class="bg-orange-500 px-8 py-4 rounded-2xl font-black uppercase text-xs"
                        >
                            Pedir Movvia
                        </button>
                    </div>
                </div>
            @endif
        @else
            {{-- CARD DE STATUS --}}
            <div class="py-2 flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-500 rounded-2xl flex items-center justify-center text-2xl animate-pulse">
                    {{ $this->activeRide->category === 'moto' ? '🏍️' : '🚗' }}
                </div>

                <div class="flex-1">
                    <p class="text-[10px] font-black text-orange-600 uppercase">
                        {{ method_exists($this->activeRide->status, 'label') ? $this->activeRide->status->label() : $this->activeRide->status->value }}
                    </p>
                    <p class="text-sm font-bold truncate">
                        Indo para: {{ $this->activeRide->destination_address }}
                    </p>
                </div>

                @if(in_array($this->activeRide->status->value, ['pending', 'accepted']))
                    <button
                        wire:click="cancelRide"
                        class="text-red-500 font-black text-[10px] uppercase underline"
                    >
                        Cancelar
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    let routingControl = null;
    let originMarker = null;
    let destinationMarker = null;
    let liveLocationMarker = null;
    let watchId = null;
    let currentSelectionTarget = 'destination';
    let typingTimerOrigin = null;
    let typingTimerDestination = null;
    let lastBoundMap = null;
    let activeMap = null;
    let originIcon = null;
    let destinationIcon = null;
    let liveIcon = null;

    let state = {
        origin: null,
        destination: null,
        originLockedManual: false
    };

    const doneTypingInterval = 800;

    function ensureLeafletReady() {
        return typeof window.L !== 'undefined';
    }

    function buildIcons() {
        if (!ensureLeafletReady()) return;

        originIcon = L.divIcon({
            className: 'custom-origin-icon',
            html: `
                <div class="w-5 h-5 bg-blue-500 border-[3px] border-white rounded-full shadow-[0_0_10px_rgba(59,130,246,0.6)] relative flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-blue-500 animate-[ping_2s_cubic-bezier(0,0,0.2,1)_infinite] opacity-40"></div>
                </div>
            `,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        destinationIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        liveIcon = L.divIcon({
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
    }

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

        if (attempts > 50) return;
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
        if (!map || !state.origin || !state.destination || !ensureLeafletReady()) return;

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
                window.dispatchEvent(new CustomEvent('route-calculated'));
            }
        });
    }

    async function setOrigin(map, lat, lng, shouldCenter = true, updateText = true) {
        const component = getComponent();
        if (!component || !map || !originIcon) return;

        state.origin = { lat: parseFloat(lat), lng: parseFloat(lng) };

        component.set('originLat', state.origin.lat);
        component.set('originLng', state.origin.lng);

        if (updateText) {
            const address = await reverseGeocode(state.origin.lat, state.origin.lng);
            component.set('origin', address);
        }

        if (originMarker) {
            originMarker.setLatLng([state.origin.lat, state.origin.lng]);
            if (!map.hasLayer(originMarker)) originMarker.addTo(map);
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
        if (!component || !map || !destinationIcon) return;

        state.destination = { lat: parseFloat(lat), lng: parseFloat(lng) };

        component.set('destinationLat', state.destination.lat);
        component.set('destinationLng', state.destination.lng);

        const address = await reverseGeocode(state.destination.lat, state.destination.lng);
        component.set('destination', address);

        if (destinationMarker) {
            destinationMarker.setLatLng([state.destination.lat, state.destination.lng]);
            if (!map.hasLayer(destinationMarker)) destinationMarker.addTo(map);
        } else {
            destinationMarker = L.marker([state.destination.lat, state.destination.lng], { icon: destinationIcon }).addTo(map);
        }

        if (shouldCenter) {
            map.setView([state.destination.lat, state.destination.lng], 16);
        }

        drawRoute(map);
    }

    function bindInputs() {
        const originInput = document.getElementById('input-origin');
        const destinationInput = document.getElementById('input-destination');

        if (originInput && !originInput.dataset.movviaBound) {
            originInput.dataset.movviaBound = '1';

            originInput.addEventListener('input', function () {
                if (!this.value.trim()) {
                    window.dispatchEvent(new CustomEvent('origin-cleared'));
                    return;
                }

                clearTimeout(typingTimerOrigin);
                typingTimerOrigin = setTimeout(async () => {
                    const result = await searchAddress(this.value);
                    if (!result || !activeMap) return;

                    state.originLockedManual = true;
                    await setOrigin(activeMap, result.lat, result.lon, true, false);

                    const component = getComponent();
                    if (component) component.set('origin', this.value);
                }, doneTypingInterval);
            });
        }

        if (destinationInput && !destinationInput.dataset.movviaBound) {
            destinationInput.dataset.movviaBound = '1';

            destinationInput.addEventListener('input', function () {
                if (!this.value.trim()) {
                    window.dispatchEvent(new CustomEvent('destination-cleared'));
                    return;
                }

                clearTimeout(typingTimerDestination);
                typingTimerDestination = setTimeout(async () => {
                    const result = await searchAddress(this.value);
                    if (!result || !activeMap) return;

                    await setDestination(activeMap, result.lat, result.lon, true);
                }, doneTypingInterval);
            });
        }
    }

    function ensureLiveMarkerOnMap(map, lat, lng) {
        if (!map || !liveIcon) return;

        if (liveLocationMarker) {
            liveLocationMarker.setLatLng([lat, lng]);
            if (!map.hasLayer(liveLocationMarker)) liveLocationMarker.addTo(map);
        } else {
            liveLocationMarker = L.marker([lat, lng], { icon: liveIcon }).addTo(map);
        }
    }

    function startLiveLocation() {
        if (!navigator.geolocation || watchId !== null) return;

        watchId = navigator.geolocation.watchPosition(
            async function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                if (!activeMap) return;

                ensureLiveMarkerOnMap(activeMap, lat, lng);

                if (!state.originLockedManual) {
                    await setOrigin(activeMap, lat, lng, !state.origin, true);
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

    function clearAllMapState() {
        if (!activeMap) return;

        if (routingControl) {
            activeMap.removeControl(routingControl);
            routingControl = null;
        }

        if (originMarker) {
            activeMap.removeLayer(originMarker);
            originMarker = null;
        }

        if (destinationMarker) {
            activeMap.removeLayer(destinationMarker);
            destinationMarker = null;
        }

        state.origin = null;
        state.destination = null;
        state.originLockedManual = false;

        const component = getComponent();
        if (component) {
            component.set('distance', 0);
            component.set('fare', 0);
        }

        window.dispatchEvent(new CustomEvent('route-cleared'));
    }

    function clearDestinationState() {
        if (!activeMap) return;

        if (routingControl) {
            activeMap.removeControl(routingControl);
            routingControl = null;
        }

        if (destinationMarker) {
            activeMap.removeLayer(destinationMarker);
            destinationMarker = null;
        }

        state.destination = null;

        const component = getComponent();
        if (component) {
            component.set('distance', 0);
            component.set('fare', 0);
        }

        window.dispatchEvent(new CustomEvent('route-cleared'));
    }

    window.confirmMapLocation = async function () {
        waitForMap(async function (map) {
            activeMap = map;

            const center = map.getCenter();

            if (currentSelectionTarget === 'origin') {
                state.originLockedManual = true;
                await setOrigin(map, center.lat, center.lng, false, true);
            } else {
                await setDestination(map, center.lat, center.lng, false);
            }

            window.dispatchEvent(new CustomEvent('route-calculated'));
            window.dispatchEvent(new CustomEvent('map-mode-changed', { detail: 'default' }));
        });
    };

    window.addEventListener('movvia-select-target', function (event) {
        currentSelectionTarget = event.detail?.target || 'destination';
    });

    window.addEventListener('ride-cancelled', function () {
        clearAllMapState();
    });

    window.addEventListener('destination-cleared', function () {
        clearDestinationState();
    });

    window.addEventListener('origin-cleared', function () {
        clearAllMapState();
    });

    function rebindIfMapChanged(map) {
        if (!map || lastBoundMap === map) return;

        lastBoundMap = map;
        activeMap = map;

        if (!originIcon || !destinationIcon || !liveIcon) {
            buildIcons();
        }

        bindInputs();
        startLiveLocation();
    }

    function init() {
        if (!ensureLeafletReady()) {
            setTimeout(init, 300);
            return;
        }

        buildIcons();

        waitForMap(function (map) {
            rebindIfMapChanged(map);
        });
    }

    init();
    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:initialized', init);
})();
</script>
@endpush