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

    public function getOptionsProperty()
    {
        if (!$this->activeRide || $this->rating === 0 || $this->rating === 5) return [];
        $isMoto = $this->activeRide->category === 'moto';

        return match ($this->rating) {
            4 => $isMoto ? ['Viseira riscada', 'Moto antiga', 'Capacete com cheiro', 'Pilotagem rápida', 'Comunicação difícil'] : ['Ar-condicionado fraco', 'Rota longa', 'Carro com cheiro', 'Som alto', 'Conversa'],
            3 => $isMoto ? ['Sem touca descartável', 'Capacete ruim', 'Moto suja', 'Freadas bruscas', 'Viseira ruim'] : ['Motorista impaciente', 'Direção brusca', 'Atraso', 'Carro sujo', 'Uso de celular'],
            2 => $isMoto ? ['Moto em mau estado', 'Grosseria', 'Manobras perigosas', 'Excesso velocidade', 'Capacete sem trava'] : ['Veículo em mau estado', 'Grosseria', 'Errou caminho', 'Direção perigosa', 'Som inadequado'],
            1 => $isMoto ? ['Sem capacete extra', 'Desrespeito', 'Empinando', 'Moto diferente', 'Direção sob efeito'] : ['Assédio ou Desrespeito', 'Imprudência grave', 'Cobrança indevida', 'Carro diferente', 'Parada indevida'],
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
            // 1. Tenta salvar a avaliação
            RideRating::create([
                'ride_id' => $this->activeRide->id,
                'driver_id' => $this->activeRide->driver_id,
                'stars' => $this->rating,
                'complaints' => $this->selectedComplaints
            ]);

            // 2. Tenta bloquear se for o caso
            if ($this->rating === 1 && $this->blockDriver) {
                DriverBlock::create([
                    'passenger_id' => Auth::id(),
                    'driver_id' => $this->activeRide->driver_id
                ]);
            }

            // 3. SE CHEGOU AQUI, DEU CERTO! Limpa tudo.
            $this->reset(['rating', 'selectedComplaints', 'blockDriver']);

            session()->flash('message', 'Avaliação enviada!');
        } catch (\Exception $e) {
            // Se der erro, ele vai te mostrar no log do Laravel
            \Log::error("Erro ao avaliar: " . $e->getMessage());

            // Forçamos o reset para a tela não travar pro usuário
            $this->reset(['rating', 'selectedComplaints', 'blockDriver']);
        }
    }
};

?>

<div wire:poll.3s>
    @if($this->activeRide)
    <div class="space-y-4">
        {{-- CARD DE STATUS --}}
        <div class="bg-white border-2 border-orange-500 rounded-[2rem] p-6 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-5 text-6xl">🚗</div>
            <div class="relative z-10">
                <h4 class="text-[10px] font-black uppercase text-orange-500 mb-1">Status da sua Viagem</h4>
                <div class="mt-2 text-gray-800">
                    @if($this->activeRide->status === RideStatus::Completed)
                    <div class="text-center py-2">
                        <p class="text-xl font-black text-green-600 uppercase italic">Pagamento Confirmado!</p>
                    </div>
                    @elseif($this->activeRide->status === RideStatus::Finished)
                    <div class="bg-green-500 rounded-2xl p-4 text-white text-center shadow-lg">
                        <p class="text-xs font-bold uppercase mb-1">Pague ao Motorista</p>
                        <p class="text-3xl font-black italic">R$ {{ number_format($this->activeRide->fare, 2, ',', '.') }}</p>
                    </div>
                    @else
                    <p class="font-black italic text-lg uppercase">{{ $this->activeRide->status->name }}...</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- SEÇÃO DE AVALIAÇÃO --}}
        @if($this->activeRide->status === RideStatus::Completed)
        <div class="bg-white rounded-[2.5rem] p-8 shadow-2xl border-2 border-orange-100 animate-fade-in">
            <div class="text-center mb-6">
                <span class="text-4xl">🏁</span>
                <h5 class="font-black text-gray-800 uppercase text-xs mt-2 italic">Viagem Concluída!</h5>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Como foi sua experiência?</p>
            </div>

            <div class="flex justify-center gap-3 mb-8">
                @foreach(range(1, 5) as $i)
                <button wire:click="setRating({{ $i }})" class="text-4xl transition-transform {{ $rating >= $i ? 'scale-110 grayscale-0' : 'grayscale opacity-30' }}">⭐</button>
                @endforeach
            </div>

            @if($rating > 0 && $rating < 5)
                <div class="grid grid-cols-1 gap-2 mb-6">
                @foreach($this->options as $opt)
                <button wire:click="toggleComplaint('{{ $opt }}')"
                    class="py-3 px-4 rounded-2xl text-[10px] font-black uppercase border-2 transition-all {{ in_array($opt, $selectedComplaints) ? 'bg-orange-500 border-orange-500 text-white shadow-lg' : 'bg-gray-50 border-gray-50 text-gray-400' }}">
                    {{ $opt }}
                </button>
                @endforeach
        </div>
        @endif

        @if($rating === 1)
        <label class="flex items-center justify-center gap-2 mb-6 p-4 bg-red-50 rounded-2xl border border-red-100 cursor-pointer">
            <input type="checkbox" wire:model="blockDriver" class="rounded text-red-600">
            <span class="text-[9px] font-black text-red-600 uppercase">Bloquear este motorista</span>
        </label>
        @endif

        @if($rating > 0)
        <button wire:click="submitRating" class="w-full py-5 bg-black text-white rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-green-600 transition-all shadow-xl">
            Confirmar Avaliação
        </button>
        @endif
    </div>
    @endif
</div>
@endif
</div>