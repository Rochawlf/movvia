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

    // Busca a corrida ativa ou a última concluída que ainda não foi avaliada
    public function getActiveRideProperty()
    {
        return Ride::where('passenger_id', Auth::id())
            ->where(function ($query) {
                $query->whereIn('status', [
                    RideStatus::Pending,
                    RideStatus::Accepted,
                    RideStatus::InProgress,
                    RideStatus::Finished
                ])
                ->orWhere(function ($q) {
                    $q->where('status', RideStatus::Completed)
                      ->whereDoesntHave('rating');
                });
            })
            ->latest()
            ->first();
    }

    public function toggleComplaint($complaint)
    {
        if (in_array($complaint, $this->selectedComplaints)) {
            $this->selectedComplaints = array_diff($this->selectedComplaints, [$complaint]);
        } else {
            $this->selectedComplaints[] = $complaint;
        }
    }

    // Retorna opções de reclamação baseadas na nota e no tipo de veículo
    public function getOptionsProperty()
    {
        if (!$this->activeRide || $this->rating === 0 || $this->rating === 5) return [];
        
        $isMoto = $this->activeRide->category === 'moto';

        return match ($this->rating) {
            4 => $isMoto ? ['Viseira riscada', 'Moto antiga', 'Capacete com cheiro', 'Pilotagem rápida', 'Comunicação difícil'] 
                         : ['Ar-condicionado fraco', 'Rota longa', 'Carro com cheiro', 'Som alto', 'Conversa excessiva'],
            3 => $isMoto ? ['Sem touca descartável', 'Capacete ruim', 'Moto suja', 'Freadas bruscas', 'Viseira ruim'] 
                         : ['Motorista impaciente', 'Direção brusca', 'Atraso', 'Carro sujo', 'Uso de celular'],
            2 => $isMoto ? ['Moto em mau estado', 'Grosseria', 'Manobras perigosas', 'Excesso velocidade', 'Capacete sem trava'] 
                         : ['Veículo em mau estado', 'Grosseria', 'Errou caminho', 'Direção perigosa', 'Som inadequado'],
            1 => $isMoto ? ['Sem capacete extra', 'Desrespeito', 'Empinando', 'Moto diferente', 'Direção sob efeito'] 
                         : ['Assédio ou Desrespeito', 'Imprudência grave', 'Cobrança indevida', 'Carro diferente', 'Parada indevida'],
            default => [],
        };
    }

    public function setRating($stars)
    {
        $this->rating = $stars;
        $this->selectedComplaints = [];
    }

    public function submitRating()
    {
        if (!$this->activeRide) return;

        try {
            // 1. Salva a avaliação vinculada à corrida
            RideRating::create([
                'ride_id' => $this->activeRide->id,
                'driver_id' => $this->activeRide->driver_id,
                'stars' => $this->rating,
                'complaints' => $this->selectedComplaints
            ]);

            // 2. Bloqueia o motorista se o passageiro desejar (apenas em notas 1)
            if ($this->rating === 1 && $this->blockDriver) {
                DriverBlock::firstOrCreate([
                    'passenger_id' => Auth::id(),
                    'driver_id' => $this->activeRide->driver_id
                ]);
            }

            $this->reset(['rating', 'selectedComplaints', 'blockDriver']);
            session()->flash('message', 'Obrigado por avaliar!');
            
        } catch (\Exception $e) {
            \Log::error("Erro ao avaliar no Movvia: " . $e->getMessage());
            $this->reset(['rating', 'selectedComplaints', 'blockDriver']);
        }
    }
};

?>

<div wire:poll.4s>
    @if($this->activeRide)
        
        {{-- CASO 1: VIAGEM EM ANDAMENTO / MOTORISTA CHEGANDO (Card Flutuante Superior) --}}
        @if($this->activeRide->status->value !== 'completed')
            <div class="fixed top-24 left-1/2 -translate-x-1/2 z-[40] w-[90%] max-w-sm animate-bounce-slow">
                <div class="bg-white/80 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-5 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.15)] flex items-center gap-5 relative overflow-hidden transition-all duration-500">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/30 to-transparent pointer-events-none"></div>
                    <div class="h-14 w-14 bg-gradient-to-br from-orange-400 to-orange-600 shadow-[0_10px_20px_-10px_rgba(249,115,22,0.6)] rounded-2xl flex items-center justify-center text-white text-3xl animate-pulse shrink-0">
                        @if($this->activeRide->status->value === 'finished') 💵 @else 🚗 @endif
                    </div>
                    <div class="flex-1 relative z-10">
                        <h4 class="text-[11px] font-black uppercase text-orange-600 tracking-widest">
                            {{ $this->activeRide->status->name }}
                        </h4>
                        @if($this->activeRide->status->value === 'finished')
                            <p class="text-3xl font-black text-gray-900 tracking-tighter drop-shadow-sm">R$ {{ number_format($this->activeRide->fare, 2, ',', '.') }}</p>
                        @else
                            <p class="text-sm font-bold text-gray-700">Motorista a caminho...</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- CASO 2: VIAGEM CONCLUÍDA (Overlay Central com Blur no Fundo) --}}
        @if($this->activeRide->status->value === 'completed')
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-6">
                {{-- FUNDO COM BLUR --}}
                <div class="absolute inset-0 bg-black/40 backdrop-blur-md animate-fade-in"></div>

                {{-- MODAL DE AVALIAÇÃO --}}
                <div class="relative bg-white/95 backdrop-blur-2xl w-full max-w-md rounded-[3rem] p-10 shadow-[0_35px_60px_-15px_rgba(0,0,0,0.5)] animate-slide-up border border-white/60">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-400 to-green-500 rounded-full mb-6 shadow-[0_15px_30px_-15px_rgba(34,197,94,0.6)]">
                            <span class="text-5xl text-white">🏁</span>
                        </div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter text-gray-900 leading-none">Viagem Finalizada</h2>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-4">Avalie o motorista</p>
                        
                        {{-- ESTRELAS --}}
                        <div class="flex justify-center gap-3 py-10">
                            @foreach(range(1, 5) as $i)
                                <button wire:click="setRating({{ $i }})" 
                                    class="text-5xl transition-all duration-300 {{ $rating >= $i ? 'scale-125 grayscale-0 drop-shadow-lg' : 'grayscale opacity-20 hover:opacity-50' }}">
                                    ⭐
                                </button>
                            @endforeach
                        </div>

                        {{-- BOTÃO FINAL --}}
                        @if($rating > 0)
                            <div class="animate-fade-in mt-4">
                                <button wire:click="submitRating" 
                                    class="w-full py-5 bg-gradient-to-r from-gray-900 to-black text-white rounded-[2rem] font-black uppercase tracking-widest text-sm hover:from-green-500 hover:to-green-600 transition-all duration-300 shadow-[0_15px_30px_-10px_rgba(0,0,0,0.4)] hover:shadow-[0_15px_30px_-10px_rgba(34,197,94,0.5)] active:scale-95">
                                    Confirmar Avaliação
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    @endif
</div>