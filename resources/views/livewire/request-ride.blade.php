<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $origin = '';
    public string $destination = '';
    public string $category = 'car';
    public $originLat, $originLng, $destinationLat, $destinationLng;

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

            if ($this->distance <= $limit) {
                $total = $basePrice;
            } else {
                $extraKm = $this->distance - $limit;
                $total = $basePrice + ($extraKm * $extraRate);
            }
        } else {
            $basePrice = 8.00;
            $extraRate = 1.30;
            $limit = 2.5;

            if ($this->distance <= $limit) {
                $total = $basePrice;
            } else {
                $extraKm = $this->distance - $limit;
                $total = $basePrice + ($extraKm * $extraRate);
            }
        }

        $this->fare = $total;
    }

    public function requestRide()
    {
        $this->validate([
            'originLat' => 'required',
            'destinationLat' => 'required',
            'distance' => 'required|numeric|min:0.1',
        ], [
            'destinationLat.required' => 'Clique no mapa para definir o destino.',
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
        ]);

        session()->flash('message', '🚗 Movvia solicitado com sucesso!');
        
        // Resetamos os campos para o próximo pedido, mas mantemos a origem do GPS
        $this->reset(['destinationLat', 'destinationLng', 'distance', 'fare', 'destination']);
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="p-6 bg-white shadow-sm rounded-xl space-y-6">
        <h3 class="text-lg font-bold text-gray-900 italic tracking-tighter">PARA ONDE VAMOS?</h3>

        {{-- MENSAGEM COM AUTO-HIDE (Alpine.js) --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-show="show" 
                 x-transition.duration.500ms
                 class="p-4 mb-4 text-sm text-green-800 rounded-2xl bg-green-50 font-bold italic shadow-sm border border-green-100">
                {{ session('message') }}
            </div>
        @endif

        <div class="space-y-4">
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 shadow-inner">
                <label class="block text-[10px] font-black text-orange-500 uppercase">Partida</label>
                <p class="text-sm font-bold text-gray-700 truncate">{{ $origin ?: 'Buscando rua...' }}</p>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 shadow-inner">
                <label class="block text-[10px] font-black text-gray-400 uppercase">Destino</label>
                <p class="text-sm font-bold text-gray-700 truncate">{{ $destination ?: 'Toque no mapa' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <button wire:click="$set('category', 'car')" class="p-3 border-2 rounded-xl flex flex-col items-center transition-all {{ $category == 'car' ? 'border-orange-500 bg-orange-50' : 'border-gray-100' }}">
                <span class="text-xl">🚗</span>
                <span class="text-[10px] font-black uppercase mt-1">Car</span>
            </button>
            <button wire:click="$set('category', 'moto')" class="p-3 border-2 rounded-xl flex flex-col items-center transition-all {{ $category == 'moto' ? 'border-orange-500 bg-orange-50' : 'border-gray-100' }}">
                <span class="text-xl">🏍️</span>
                <span class="text-[10px] font-black uppercase mt-1">Moto</span>
            </button>
            <button wire:click="$set('category', 'delivery')" class="p-3 border-2 rounded-xl flex flex-col items-center transition-all {{ $category == 'delivery' ? 'border-orange-500 bg-orange-50' : 'border-gray-100' }}">
                <span class="text-xl">📦</span>
                <span class="text-[10px] font-black uppercase mt-1">Envio</span>
            </button>
        </div>

        @if($fare > 0)
        <div class="py-4 border-t border-dashed space-y-3">
            <div class="flex justify-between items-center text-gray-500 text-[10px] font-black uppercase">
                <span>Distância estimada:</span>
                <span>{{ number_format($distance, 2) }} km</span>
            </div>

            <div class="flex justify-between items-center p-4 bg-gray-900 rounded-2xl shadow-xl">
                <div>
                    <p class="text-[9px] font-black text-orange-400 uppercase leading-none">Preço Estimado</p>
                    <p class="text-2xl font-black text-white tracking-tighter">
                        R$ {{ number_format($fare, 2, ',', '.') }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-[8px] bg-orange-500 text-white px-2 py-1 rounded-md font-black italic">
                        VIA PIX/DINHEIRO
                    </span>
                </div>
            </div>
        </div>
        @endif

        <button wire:click="requestRide" @if($fare==0) disabled @endif class="w-full py-5 bg-black text-white rounded-2xl font-black uppercase tracking-widest hover:bg-orange-600 transition shadow-2xl disabled:opacity-30">
            Confirmar Movvia
        </button>
    </div>

    <div class="md:col-span-2 h-[500px] md:h-full bg-gray-100 rounded-3xl overflow-hidden shadow-2xl relative border-4 border-white" id="map" wire:ignore></div>
</div>

<script>
    document.addEventListener('livewire:navigated', () => {
        // Inicializa o mapa focado em Camaçari se não tiver GPS
        const map = L.map('map', { zoomControl: false }).setView([-12.6975, -38.3242], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let control;
        let originMarker;

        const redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });

        async function updateAddressField(lat, lng, field) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                const street = data.address.road || data.address.pedestrian || data.address.suburb || "Rua desconhecida";
                const number = data.address.house_number ? `, ${data.address.house_number}` : "";
                
                @this.set(field, street + number);
            } catch (e) {
                @this.set(field, "Localização selecionada");
            }
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                const { latitude, longitude } = position.coords;
                @this.set('originLat', latitude);
                @this.set('originLng', longitude);
                
                updateAddressField(latitude, longitude, 'origin');

                map.setView([latitude, longitude], 16);
                if (originMarker) map.removeLayer(originMarker);
                originMarker = L.marker([latitude, longitude]).addTo(map).bindPopup('Você está aqui').openPopup();
            });
        }

        map.on('click', function(e) {
            const { lat, lng } = e.latlng;

            if (control) map.removeControl(control);

            control = L.Routing.control({
                waypoints: [
                    L.latLng(@this.originLat, @this.originLng),
                    L.latLng(lat, lng)
                ],
                lineOptions: { styles: [{ color: '#EA580C', weight: 7 }] },
                createMarker: function(i, wp) {
                    if (i === 1) return L.marker(wp.latLng, { icon: redIcon }).bindPopup('Destino');
                    return null;
                },
                addWaypoints: false,
                draggableWaypoints: false
            }).addTo(map);

            control.on('routesfound', function(event) {
                const distance = event.routes[0].summary.totalDistance / 1000;
                @this.set('distance', distance);
                @this.set('destinationLat', lat);
                @this.set('destinationLng', lng);
                
                updateAddressField(lat, lng, 'destination');
            });
        });
    });
</script>