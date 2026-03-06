<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ride;

class RecentRides extends Component
{
    public $limit = 5;
    
    public function render()
    {
        $rides = Ride::where('passenger_id', auth()->id())
            ->orWhere('driver_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit($this->limit)
            ->get();
            
        return view('livewire.recent-rides', [
            'rides' => $rides
        ]);
    }

    public function selectRide($lat, $lng, $address)
    {
        $this->dispatch('ride-selected-from-history', lat: $lat, lng: $lng, address: $address);
    }
}