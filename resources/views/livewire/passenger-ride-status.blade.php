<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Models\RideRating;
use App\Models\DriverBlock;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public int $rating = 0;
    public array $selectedComplaints = [];
    public bool $blockDriver = false;

    public function getActiveRideProperty()
    {
        return Ride::query()
            ->with(['driver', 'rating'])
            ->where('passenger_id', Auth::id())
            ->where(function ($query) {
                $query->whereIn('status', [
                    RideStatus::Pending,
                    RideStatus::Accepted,
                    RideStatus::InProgress,
                    RideStatus::Finished,
                ])->orWhere(function ($q) {
                    $q->where('status', RideStatus::Completed)
                      ->whereDoesntHave('rating');
                });
            })
            ->latest()
            ->first();
    }

    public function toggleComplaint(string $complaint): void
    {
        if (in_array($complaint, $this->selectedComplaints, true)) {
            $this->selectedComplaints = array_values(array_diff($this->selectedComplaints, [$complaint]));
            return;
        }

        $this->selectedComplaints[] = $complaint;
    }

    public function getOptionsProperty(): array
    {
        if (!$this->activeRide || $this->rating === 0 || $this->rating === 5) {
            return [];
        }

        $isMoto = $this->activeRide->category === 'moto';

        return match ($this->rating) {
            4 => $isMoto
                ? ['Pilotagem rápida', 'Capacete com cheiro', 'Moto antiga', 'Comunicação difícil']
                : ['Ar fraco', 'Rota longa', 'Carro com cheiro', 'Som alto'],
            3 => $isMoto
                ? ['Capacete ruim', 'Moto suja', 'Freadas bruscas', 'Viseira ruim']
                : ['Motorista impaciente', 'Direção brusca', 'Atraso', 'Carro sujo'],
            2 => $isMoto
                ? ['Moto em mau estado', 'Grosseria', 'Manobras perigosas', 'Excesso de velocidade']
                : ['Veículo em mau estado', 'Grosseria', 'Errou caminho', 'Direção perigosa'],
            1 => $isMoto
                ? ['Sem capacete extra', 'Desrespeito', 'Moto diferente', 'Direção perigosa']
                : ['Desrespeito', 'Imprudência grave', 'Cobrança indevida', 'Carro diferente'],
            default => [],
        };
    }

    public function setRating(int $stars): void
    {
        $this->rating = $stars;
        $this->selectedComplaints = [];
        $this->blockDriver = false;
    }

    public function submitRating(): void
    {
        if (!$this->activeRide || $this->rating < 1 || $this->rating > 5) {
            return;
        }

        try {
            RideRating::updateOrCreate(
                ['ride_id' => $this->activeRide->id],
                [
                    'ride_id' => $this->activeRide->id,
                    'passenger_id' => Auth::id(),
                    'driver_id' => $this->activeRide->driver_id,
                    'stars' => $this->rating,
                    'complaints' => $this->selectedComplaints,
                ]
            );

            if ($this->rating === 1 && $this->blockDriver && $this->activeRide->driver_id) {
                DriverBlock::firstOrCreate([
                    'passenger_id' => Auth::id(),
                    'driver_id' => $this->activeRide->driver_id,
                ]);
            }

            $this->reset(['rating', 'selectedComplaints', 'blockDriver']);
            session()->flash('message', 'Obrigado por avaliar!');
            $this->dispatch('rating-submitted');
        } catch (\Throwable $e) {
            Log::error('Erro ao avaliar corrida', [
                'message' => $e->getMessage(),
                'ride_id' => $this->activeRide?->id,
                'passenger_id' => Auth::id(),
            ]);

            session()->flash('message', 'Não foi possível enviar sua avaliação agora.');
            $this->reset(['rating', 'selectedComplaints', 'blockDriver']);
        }
    }
};

?>
<!-- fgdfgdf -->
<div wire:poll.4s>
    @if($this->activeRide)

        <!-- {{-- STATUS FLUTUANTE DURANTE A CORRIDA --}} -->
        @if($this->activeRide->status->value !== 'completed')
            <div class="fixed top-20 left-1/2 -translate-x-1/2 z-[70] w-[92%] max-w-[320px] pointer-events-none">
                <div class="bg-white/90 backdrop-blur-xl border border-white/60 rounded-[1.5rem] px-4 py-3 shadow-[0_12px_30px_-12px_rgba(0,0,0,0.22)] flex items-center gap-3">
                    <div class="h-11 w-11 rounded-2xl flex items-center justify-center text-white text-xl shrink-0
                        {{ $this->activeRide->status->value === 'finished' ? 'bg-green-500' : 'bg-orange-500' }}">
                        @if($this->activeRide->status->value === 'finished')
                            💵
                        @elseif($this->activeRide->category === 'moto')
                            🏍️
                        @else
                            🚗
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-600">
                            {{ $this->activeRide->status->name }}
                        </p>

                        @if($this->activeRide->status->value === 'pending')
                            <p class="text-sm font-bold text-gray-800 truncate">Procurando motorista...</p>
                        @elseif($this->activeRide->status->value === 'accepted')
                            <p class="text-sm font-bold text-gray-800 truncate">Motorista a caminho</p>
                        @elseif($this->activeRide->status->value === 'in_progress')
                            <p class="text-sm font-bold text-gray-800 truncate">Viagem em andamento</p>
                        @elseif($this->activeRide->status->value === 'finished')
                            <p class="text-lg font-black text-gray-900">R$ {{ number_format($this->activeRide->fare, 2, ',', '.') }}</p>
                        @else
                            <p class="text-sm font-bold text-gray-800 truncate">Status atualizado</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL DE AVALIAÇÃO --}}
        @if($this->activeRide->status->value === 'completed')
            <div class="fixed inset-0 z-[120] flex items-end justify-center p-0 sm:p-4 pointer-events-none">
    <div class="absolute inset-0 bg-black/45 backdrop-blur-sm pointer-events-auto"></div>

    <div class="relative w-full max-w-md bg-white rounded-t-[2rem] sm:rounded-[2rem] shadow-2xl border border-white/60 px-5 pt-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] pointer-events-auto">
                    <div class="flex justify-center mb-3">
                        <div class="w-10 h-1.5 bg-gray-300 rounded-full"></div>
                    </div>

                    <div class="text-center mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-500 rounded-full mb-3 shadow-lg">
                            <span class="text-3xl text-white">🏁</span>
                        </div>

                        <h2 class="text-xl font-black text-gray-900 tracking-tight">
                            Viagem finalizada
                        </h2>

                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                            Avalie seu motorista
                        </p>
                    </div>

                    @if (session()->has('message'))
                        <div class="mb-3 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-bold">
                            {{ session('message') }}
                        </div>
                    @endif

                    {{-- ESTRELAS --}}
                    <div class="flex justify-center gap-2 py-3">
                        @foreach(range(1, 5) as $i)
                            <button
                                type="button"
                                wire:click="setRating({{ $i }})"
                                class="text-4xl transition-all duration-200 {{ $rating >= $i ? 'scale-110 opacity-100' : 'opacity-25' }}"
                            >
                                ⭐
                            </button>
                        @endforeach
                    </div>

                    {{-- RECLAMAÇÕES --}}
                    @if($rating > 0 && $rating < 5 && count($this->options))
                        <div class="mt-4">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">
                                O que aconteceu?
                            </p>

                            <div class="grid grid-cols-2 gap-2">
                                @foreach($this->options as $option)
                                    <button
                                        type="button"
                                        wire:click="toggleComplaint('{{ addslashes($option) }}')"
                                        class="px-3 py-2 rounded-xl text-[11px] font-bold border transition-all
                                            {{ in_array($option, $selectedComplaints, true)
                                                ? 'bg-orange-50 border-orange-400 text-orange-600'
                                                : 'bg-gray-50 border-gray-200 text-gray-600' }}"
                                    >
                                        {{ $option }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- BLOQUEAR MOTORISTA --}}
                    @if($rating === 1)
                        <label class="mt-4 flex items-start gap-3 p-3 rounded-xl bg-red-50 border border-red-100 cursor-pointer">
                            <input type="checkbox" wire:model.live="blockDriver" class="mt-1 rounded border-gray-300 text-red-500 focus:ring-red-400">
                            <div>
                                <p class="text-xs font-black text-red-600 uppercase tracking-widest">Bloquear motorista</p>
                                <p class="text-[11px] text-red-500 font-medium">Esse motorista não poderá receber suas próximas corridas.</p>
                            </div>
                        </label>
                    @endif

                    {{-- BOTÃO FINAL --}}
                    @if($rating > 0)
                        <div class="mt-5">
                            <button
                                type="button"
                                wire:click="submitRating"
                                class="w-full py-4 bg-gradient-to-r from-gray-900 to-black text-white rounded-2xl font-black uppercase tracking-widest text-sm active:scale-95 transition-all"
                            >
                                Confirmar avaliação
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    @endif
</div>