<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public function getTripProperty()
    {
        return Ride::where('driver_id', Auth::id())
            ->whereIn('status', [RideStatus::Accepted, RideStatus::InProgress])
            ->first();
    }

    public function startRide() {
        $this->trip->update(['status' => RideStatus::InProgress]);
    }

    public function completeRide() {
        $this->trip->update(['status' => RideStatus::Completed]);
    }
}; ?>

<div>
    @if($this->trip)
        <div class="p-6 bg-green-900 text-white rounded-lg shadow-lg">
            <h3 class="text-lg font-bold mb-4">📍 Viagem em Curso</h3>
            <p>Passageiro: {{ $this->trip->passenger->name }}</p>
            <p class="text-sm opacity-75">Destino: {{ $this->trip->destination_address }}</p>

            <div class="mt-6 flex gap-2">
                @if($this->trip->status === \App\Enums\RideStatus::Accepted)
                    <button wire:click="startRide" class="bg-white text-green-900 px-4 py-2 rounded font-bold w-full">INICIAR CORRIDA</button>
                @else
                    <button wire:click="completeRide" class="bg-yellow-500 text-black px-4 py-2 rounded font-bold w-full">FINALIZAR CORRIDA</button>
                @endif
            </div>
        </div>
    @endif
</div>