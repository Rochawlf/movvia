<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Models\RideRating;
use App\Models\DriverBlock;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public int $rating = 0;
    public array $selectedComplaints = [];
    public bool $blockDriver = false;

    // REQUISITO 5: Foca apenas em corridas completadas que precisam de avaliação
    public function getCompletedRideProperty()
    {
        return Ride::query()
            ->with(['driver', 'rating'])
            ->where('passenger_id', Auth::id())
            ->where('status', RideStatus::Completed)
            ->whereDoesntHave('rating')
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
        if (!$this->completedRide || $this->rating === 0 || $this->rating === 5) {
            return [];
        }

        $isMoto = $this->completedRide->category === 'moto';

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
        $ride = $this->completedRide;

        if (!$ride || $this->rating < 1 || $this->rating > 5) {
            return;
        }

        RideRating::updateOrCreate(
            ['ride_id' => $ride->id],
            [
                'passenger_id' => Auth::id(),
                'driver_id' => $ride->driver_id,
                'stars' => $this->rating,
                'complaints' => $this->selectedComplaints,
            ]
        );

        if ($this->rating === 1 && $this->blockDriver && $ride->driver_id) {
            DriverBlock::firstOrCreate([
                'passenger_id' => Auth::id(),
                'driver_id' => $ride->driver_id,
            ]);
        }

        $this->reset(['rating', 'selectedComplaints', 'blockDriver']);
        session()->flash('message', 'Obrigado por avaliar!');
    }
};

?>

<div wire:poll.5s>
    @if($this->completedRide)
        {{-- MODAL DE AVALIAÇÃO --}}
        <div class="fixed inset-0 z-[200] flex items-end justify-center p-0 sm:p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div 
                x-data="{ show: false }" 
                x-init="setTimeout(() => show = true, 100)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                class="relative w-full max-w-md bg-white rounded-t-[2.5rem] sm:rounded-[2.5rem] shadow-2xl px-6 pt-4 pb-[max(2rem,env(safe-area-inset-bottom))]"
            >
                <div class="flex justify-center mb-6">
                    <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
                </div>

                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-full mb-4 text-4xl">
                        ⭐
                    </div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Como foi sua viagem?</h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Sua avaliação ajuda o Movvia a melhorar</p>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl text-xs font-bold text-center">
                        {{ session('message') }}
                    </div>
                @endif

                {{-- SELEÇÃO DE ESTRELAS --}}
                <div class="flex justify-center gap-3 py-4">
                    @foreach(range(1, 5) as $i)
                        <button
                            type="button"
                            wire:click="setRating({{ $i }})"
                            class="text-4xl transition-all duration-200 transform hover:scale-125 {{ $rating >= $i ? 'grayscale-0 opacity-100' : 'grayscale opacity-30' }}"
                        >
                            ⭐
                        </button>
                    @endforeach
                </div>

                {{-- TAGS DE FEEDBACK NEGATIVO --}}
                @if($rating > 0 && $rating < 5 && count($this->options))
                    <div class="mt-6 animate-fadeIn">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 text-center">O que poderia ter sido melhor?</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($this->options as $option)
                                <button
                                    type="button"
                                    wire:click="toggleComplaint('{{ addslashes($option) }}')"
                                    class="px-3 py-3 rounded-xl text-[10px] font-black border-2 transition-all
                                        {{ in_array($option, $selectedComplaints, true)
                                            ? 'bg-orange-500 border-orange-500 text-white'
                                            : 'bg-gray-50 border-gray-100 text-gray-500' }}"
                                >
                                    {{ $option }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- OPÇÃO DE BLOQUEIO PARA 1 ESTRELA --}}
                @if($rating === 1)
                    <div class="mt-6 p-4 rounded-2xl bg-red-50 border-2 border-red-100 flex items-start gap-3">
                        <input type="checkbox" wire:model.live="blockDriver" id="block" class="mt-1 rounded text-red-500 focus:ring-red-500">
                        <label for="block" class="cursor-pointer">
                            <p class="text-xs font-black text-red-600 uppercase">Não viajar com este motorista</p>
                            <p class="text-[10px] text-red-400 font-bold leading-tight">Bloquear o motorista para que ele não receba mais suas chamadas.</p>
                        </label>
                    </div>
                @endif

                {{-- BOTÃO DE CONFIRMAÇÃO --}}
                @if($rating > 0)
                    <div class="mt-8">
                        <button
                            type="button"
                            wire:click="submitRating"
                            class="w-full py-5 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-xl active:scale-95 transition-all"
                        >
                            Enviar Avaliação
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>