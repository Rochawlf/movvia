<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    // Declare todas as propriedades necessárias
    public string $origin = '';           // <-- ESTAVA FALTANDO
    public string $destination = '';
    public string $category = 'car';
    public $originLat;
    public $originLng;
    public $destinationLat;
    public $destinationLng;
    public $distance = 0;
    public $fare = 0;

    // Seus métodos existentes...
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
        
        // Reset dos campos
        $this->reset(['destination', 'destinationLat', 'destinationLng', 'distance', 'fare']);
    }
};