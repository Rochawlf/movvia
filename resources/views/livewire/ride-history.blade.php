<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new class extends Component {
    public string $period = 'day'; // Filtro padrão: Hoje

    public function getRidesProperty()
    {
        $user = Auth::user();
        $query = Ride::latest();

        // 1. Filtro de Segurança (Motorista vs Passageiro)
        if ($user->role === 'driver') {
            $query->where('driver_id', $user->id);
        } else {
            $query->where('passenger_id', $user->id);
        }

        // 2. Lógica dos Filtros de Tempo
        $query = match($this->period) {
            'day' => $query->whereDate('created_at', Carbon::today()),
            'week' => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year),
            'year' => $query->whereYear('created_at', Carbon::now()->year),
            default => $query,
        };

        return $query->get();
    }

    public function getTotalProperty()
    {
        // Soma o total do período selecionado
        return $this->rides->where('status', RideStatus::Completed)->sum('fare');
    }
}; ?>

<div class="space-y-6">
    {{-- BOTÕES DE FILTRO --}}
    <div class="flex bg-white p-1 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
        @foreach(['day' => 'Hoje', 'week' => 'Semana', 'month' => 'Mês', 'year' => 'Ano'] as $key => $label)
            <button wire:click="$set('period', '{{ $key }}')" 
                class="flex-1 py-3 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $period === $key ? 'bg-black text-white shadow-lg' : 'text-gray-400 hover:text-gray-600' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- RESUMO DO PERÍODO --}}
    <div class="bg-gray-900 rounded-[2rem] p-6 text-white flex justify-between items-center shadow-xl">
        <div>
            <p class="text-[9px] font-black uppercase text-orange-400 opacity-80">Total no Período</p>
            <p class="text-3xl font-black tracking-tighter">R$ {{ number_format($this->total, 2, ',', '.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-[9px] font-black uppercase text-gray-400">Viagens</p>
            <p class="text-xl font-black">{{ $this->rides->count() }}</p>
        </div>
    </div>

    {{-- LISTA DE VIAGENS --}}
    <div class="space-y-4">
        @forelse($this->rides as $ride)
            <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                        {{ $ride->category === 'moto' ? '🏍️' : '🚗' }}
                    </div>
                    <div>
                        <p class="text-xs font-black text-gray-900 truncate w-40">{{ $ride->destination_address }}</p>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">{{ $ride->created_at->format('H:i • d/m') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-black {{ $ride->status === RideStatus::Completed ? 'text-green-600' : 'text-red-500' }}">
                        R$ {{ number_format($ride->fare, 2, ',', '.') }}
                    </p>
                    <span class="text-[8px] font-black uppercase">{{ $ride->status->name }}</span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-[2rem] py-20 text-center border-2 border-dashed border-gray-100">
                <p class="text-gray-400 font-bold italic">Nenhuma corrida neste período em Camaçari.</p>
            </div>
        @endforelse
    </div>
</div>