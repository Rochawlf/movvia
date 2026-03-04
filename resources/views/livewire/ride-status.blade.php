<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    // Busca a corrida mais recente do passageiro que não esteja finalizada ou cancelada
    public function getActiveRideProperty()
    {
        return Ride::where('passenger_id', Auth::id())
            ->whereIn('status', [RideStatus::Pending, RideStatus::Accepted, RideStatus::InProgress])
            ->latest()
            ->first();
    }
}; ?>

<div wire:poll.3s> {{-- Atualiza a cada 3 segundos --}}
    @if($this->activeRide)
        <div class="p-6 bg-indigo-50 dark:bg-gray-700 border-l-4 border-indigo-500 rounded-lg shadow-inner">
            <h4 class="font-bold text-indigo-900 dark:text-indigo-200">Status da sua Viagem</h4>
            
            <div class="mt-2">
                @if($this->activeRide->status === \App\Enums\RideStatus::Pending)
                    <p class="animate-pulse text-gray-600 dark:text-gray-300">🔍 Procurando motoristas próximos...</p>
                @elseif($this->activeRide->status === \App\Enums\RideStatus::Accepted)
                    <p class="text-green-600 font-bold font-lg">✅ O motorista {{ $this->activeRide->driver->name }} aceitou! Está a caminho.</p>
                @elseif($this->activeRide->status === \App\Enums\RideStatus::InProgress)
                    <p class="text-blue-600 font-bold">🚗 Você está em viagem para {{ $this->activeRide->destination_address }}.</p>
                @endif
            </div>
            
            <div class="mt-4 text-xs text-gray-500">
                De: {{ $this->activeRide->origin_address }} <br>
                Para: {{ $this->activeRide->destination_address }}
            </div>
        </div>
    @endif
</div>