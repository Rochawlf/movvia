<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $origin = '';
    public string $destination = '';
    public string $category = 'car';
    public $originLat;
    public $originLng;
    public $destinationLat;
    public $destinationLng;
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
        if ($this->distance <= 0) {
            $this->fare = 0;
            return;
        }

        $total = 0;
        if ($this->category === 'moto' || $this->category === 'delivery') {
            $basePrice = 5.20;
            $extraRate = 0.75;
            $limit = 2.7;
            $total = ($this->distance <= $limit) ? $basePrice : $basePrice + (($this->distance - $limit) * $extraRate);
        } else {
            $basePrice = 8.00;
            $extraRate = 1.30;
            $limit = 2.5;
            $total = ($this->distance <= $limit) ? $basePrice : $basePrice + (($this->distance - $limit) * $extraRate);
        }

        $this->fare = round($total, 2);
    }

    public function requestRide()
    {
        $this->validate([
            'originLat' => 'required',
            'destinationLat' => 'required',
            'distance' => 'required|numeric|min:0.1',
        ], [
            'destinationLat.required' => 'Selecione o destino no mapa.',
            'distance.min' => 'A distância é muito curta.'
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
        $this->reset(['destination', 'destinationLat', 'destinationLng', 'distance', 'fare']);
    }
}; ?>

<div class="max-w-[1600px] mx-auto p-4 lg:p-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- COLUNA DA ESQUERDA: FORMULÁRIO --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-[3rem] shadow-2xl border border-gray-100 p-8 lg:p-10 space-y-8">
                
                <h2 class="text-2xl font-black text-gray-900 italic tracking-tighter uppercase flex items-center gap-3">
                    <span class="w-2 h-8 bg-orange-500 rounded-full"></span>
                    Movvia <span class="text-orange-500">App</span>
                </h2>

                @if (session()->has('message'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" 
                         class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl font-bold text-sm animate-bounce">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="space-y-6">
                    {{-- PARTIDA --}}
                    <div class="relative pl-10">
                        <div class="absolute left-[13px] top-8 bottom-8 w-0.5 border-l-2 border-dashed border-gray-200"></div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-500 uppercase tracking-widest italic ml-1">Sua Partida</label>
                            <div class="relative">
                                <div class="absolute -left-[35px] top-1/2 -translate-y-1/2 h-4 w-4 rounded-full bg-blue-500 border-4 border-white shadow-md"></div>
                                <input type="text" wire:model="origin" placeholder="Buscando endereço..."
                                    class="w-full bg-blue-50/30 border-2 border-blue-100 rounded-2xl py-4 px-6 text-sm font-black text-gray-700 focus:border-blue-500 focus:ring-0 transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- DESTINO --}}
                    <div class="relative pl-10">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-orange-500 uppercase tracking-widest italic ml-1">Para onde vamos?</label>
                            <div class="relative">
                                <div class="absolute -left-[35px] top-1/2 -translate-y-1/2 h-4 w-4 rounded-full bg-orange-500 border-4 border-white shadow-md"></div>
                                <input type="text" wire:model="destination" readonly placeholder="Clique no mapa para definir"
                                    class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl py-4 px-6 text-sm font-black text-gray-700 cursor-default">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SELEÇÃO DE VEÍCULO --}}
                <div class="grid grid-cols-2 gap-4">
                    <button wire:click="$set('category', 'car')" 
                        class="flex items-center gap-4 p-4 rounded-3xl border-2 transition-all {{ $category === 'car' ? 'border-orange-500 bg-orange-50 shadow-lg' : 'border-gray-100 bg-gray-50' }}">
                        <span class="text-3xl">🚗</span>
                        <div class="text-left">
                            <p class="text-[10px] font-black uppercase italic leading-none {{ $category === 'car' ? 'text-orange-600' : 'text-gray-400' }}">Carro</p>
                            <p class="text-[8px] font-bold text-gray-400 mt-1">R$ 8,00 base</p>
                        </div>
                    </button>
                    <button wire:click="$set('category', 'moto')" 
                        class="flex items-center gap-4 p-4 rounded-3xl border-2 transition-all {{ $category === 'moto' ? 'border-orange-500 bg-orange-50 shadow-lg' : 'border-gray-100 bg-gray-50' }}">
                        <span class="text-3xl">🏍️</span>
                        <div class="text-left">
                            <p class="text-[10px] font-black uppercase italic leading-none {{ $category === 'moto' ? 'text-orange-600' : 'text-gray-400' }}">Moto</p>
                            <p class="text-[8px] font-bold text-gray-400 mt-1">R$ 5,20 base</p>
                        </div>
                    </button>
                </div>

                <div class="space-y-4 pt-4">
                    @if($fare > 0)
                        <div class="flex justify-between items-end bg-gray-900 rounded-[2rem] p-6 text-white shadow-xl">
                            <div>
                                <p class="text-[9px] font-black text-orange-400 uppercase tracking-widest mb-1 italic">Total Estimado</p>
                                <p class="text-3xl font-black italic tracking-tighter">R$ {{ number_format($fare, 2, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-bold text-gray-400 uppercase">{{ number_format($distance, 1) }} km</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">~{{ ceil($distance * 3) }} min</p>
                            </div>
                        </div>
                    @endif

                    <button wire:click="requestRide" @if($fare == 0) disabled @endif
                        class="w-full bg-orange-500 text-white py-6 rounded-[2rem] font-black italic uppercase tracking-widest text-lg shadow-xl hover:bg-orange-600 active:scale-95 transition-all disabled:opacity-50">
                        Pedir Movvia
                    </button>
                </div>
            </div>
        </div>

        {{-- COLUNA DA DIREITA: MAPA --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-[4rem] shadow-2xl border-8 border-white overflow-hidden relative group">
                <div id="ride-request-map" class="h-[600px] lg:h-[800px] w-full bg-gray-100" wire:ignore></div>
                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 bg-black/70 backdrop-blur-md px-6 py-3 rounded-full text-[10px] font-black text-white uppercase tracking-[0.2em] pointer-events-none transition-opacity group-hover:opacity-0">
                    Toque no mapa para definir o destino em Camaçari
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:navigated', function() {
        if (window.rideMap) return;

        window.rideMap = L.map('ride-request-map', { zoomControl: false }).setView([-12.6975, -38.3242], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.rideMap);

        let routingControl = null;
        let originMarker = null;
        let typingTimer;
        const doneTypingInterval = 1500;

        const destIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        async function updateAddress(lat, lng, field) {
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
                const data = await res.json();
                let addr = data.address.road || data.display_name.split(',')[0];
                if (data.address.house_number) addr += `, ${data.address.house_number}`;
                @this.set(field, addr);
            } catch (e) { @this.set(field, 'Localização Manual'); }
        }

        // Lógica de busca por digitação (DEBOUNCE)
        const originInput = document.querySelector('input[wire\\:model="origin"]');
        if (originInput) {
            originInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(async () => {
                    const query = this.value;
                    if (query.length < 5) return;

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=1&countrycodes=br`);
                        const data = await res.json();

                        if (data.length > 0) {
                            const { lat, lon } = data[0];
                            @this.set('originLat', lat);
                            @this.set('originLng', lon);

                            window.rideMap.setView([lat, lon], 16);
                            if (originMarker) {
                                originMarker.setLatLng([lat, lon]);
                            } else {
                                originMarker = L.marker([lat, lon]).addTo(window.rideMap);
                            }
                        }
                    } catch (error) { console.error('Erro na busca:', error); }
                }, doneTypingInterval);
            });
        }

        // GPS Inicial
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((p) => {
                const { latitude: lat, longitude: lng } = p.coords;
                @this.set('originLat', lat); @this.set('originLng', lng);
                updateAddress(lat, lng, 'origin');
                window.rideMap.setView([lat, lng], 16);
                if (originMarker) window.rideMap.removeLayer(originMarker);
                originMarker = L.marker([lat, lng]).addTo(window.rideMap).bindPopup('Sua localização').openPopup();
            });
        }

        // Clique para destino
        window.rideMap.on('click', function(e) {
            const { lat, lng } = e.latlng;
            if (routingControl) window.rideMap.removeControl(routingControl);

            routingControl = L.Routing.control({
                waypoints: [L.latLng(@this.originLat, @this.originLng), L.latLng(lat, lng)],
                lineOptions: { styles: [{ color: '#f97316', weight: 8, opacity: 0.8 }] },
                createMarker: (i, wp) => i === 1 ? L.marker(wp.latLng, { icon: destIcon }) : null,
                addWaypoints: false, draggableWaypoints: false, show: false, fitSelectedRoutes: true
            }).addTo(window.rideMap);

            routingControl.on('routesfound', (ev) => {
                const route = ev.routes[0];
                @this.set('distance', route.summary.totalDistance / 1000);
                @this.set('destinationLat', lat); @this.set('destinationLng', lng);
                updateAddress(lat, lng, 'destination');
            });
        });
    });
</script>
@endpush