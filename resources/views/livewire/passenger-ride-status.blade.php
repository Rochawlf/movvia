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

<div wire:poll.5s class="pointer-events-none">
    @if($this->completedRide)
        {{-- MODAL DE AVALIAÇÃO --}}
        <div class="fixed inset-0 z-[220] flex items-end justify-center p-0 sm:p-4 pointer-events-auto">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-md"></div>

            <div
                x-data="{ show: false }"
                x-init="setTimeout(() => show = true, 100)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                class="relative w-full max-w-md rounded-t-[2.5rem] sm:rounded-[2.5rem] overflow-hidden"
            >
                <div class="absolute inset-0 bg-[#161616]/96 backdrop-blur-3xl border border-white/10 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.8)]"></div>

                <div class="relative px-6 pt-4 pb-[max(2rem,env(safe-area-inset-bottom))]">
                    <div class="flex justify-center mb-6">
                        <div class="w-12 h-1.5 bg-white/20 rounded-full"></div>
                    </div>

                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-orange-500/20 to-blue-600/20 border border-white/10 text-4xl mb-4 shadow-xl">
                            ⭐
                        </div>

                        <h2 class="text-2xl font-black text-white tracking-tight">
                            Como foi sua viagem?
                        </h2>

                        <p class="text-[10px] font-black text-orange-300 uppercase tracking-[0.22em] mt-2">
                            Sua avaliação ajuda o Movvia a melhorar
                        </p>
                    </div>

                    @if (session()->has('message'))
                        <div class="mb-4 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 text-emerald-300 text-xs font-black text-center">
                            {{ session('message') }}
                        </div>
                    @endif

                    {{-- ESTRELAS --}}
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

                    {{-- FEEDBACK NEGATIVO --}}
                    @if($rating > 0 && $rating < 5 && count($this->options))
                        <div class="mt-6">
                            <p class="text-[10px] font-black text-white/45 uppercase tracking-[0.18em] mb-3 text-center">
                                O que poderia ter sido melhor?
                            </p>

                            <div class="grid grid-cols-2 gap-2">
                                @foreach($this->options as $option)
                                    <button
                                        type="button"
                                        wire:click="toggleComplaint('{{ addslashes($option) }}')"
                                        class="px-3 py-3 rounded-2xl text-[10px] font-black border transition-all
                                            {{ in_array($option, $selectedComplaints, true)
                                                ? 'bg-gradient-to-r from-orange-500 to-blue-600 border-transparent text-white shadow-[0_12px_25px_-12px_rgba(37,99,235,0.45)]'
                                                : 'bg-white/5 border-white/10 text-white/65 hover:bg-white/8' }}"
                                    >
                                        {{ $option }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- BLOQUEAR MOTORISTA --}}
                    @if($rating === 1)
                        <div class="mt-6 p-4 rounded-[1.6rem] bg-red-500/10 border border-red-400/20 flex items-start gap-3">
                            <input
                                type="checkbox"
                                wire:model.live="blockDriver"
                                id="block"
                                class="mt-1 rounded border-red-300 bg-transparent text-red-500 focus:ring-red-500"
                            >

                            <label for="block" class="cursor-pointer">
                                <p class="text-xs font-black text-red-300 uppercase tracking-widest">
                                    Não viajar com este motorista
                                </p>
                                <p class="text-[10px] text-red-200/70 font-bold leading-tight mt-1">
                                    Bloquear o motorista para que ele não receba mais suas chamadas.
                                </p>
                            </label>
                        </div>
                    @endif

                    {{-- BOTÃO --}}
                    @if($rating > 0)
                        <div class="mt-8">
                            <button
                                type="button"
                                wire:click="submitRating"
                                class="w-full py-5 bg-gradient-to-r from-orange-500 to-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-sm shadow-[0_15px_30px_-10px_rgba(37,99,235,0.35)] active:scale-95 transition-all"
                            >
                                Enviar avaliação
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>