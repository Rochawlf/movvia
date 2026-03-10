<?php
use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Carbon\Carbon;

new class extends Component {
    public Ride $ride;
    public bool $showSummary = false;
    public string $paymentMethod = 'money';

    // Sincroniza o estado caso o motorista ou sistema mude o status
    protected function getListeners() {
        return [
            "echo:rides.{$this->ride->id},RideStatusChanged" => '$refresh',
        ];
    }

    public function mount(Ride $ride)
    {
        $this->ride = $ride;
        // Se a corrida já estiver finalizada mas não completada, mostra o resumo
        if ($this->ride->status === RideStatus::Finished) {
            $this->showSummary = true;
        }
    }

    // REQUISITO 1: Cancelamento pelo passageiro
    public function cancelRide()
    {
        if (in_array($this->ride->status, [RideStatus::Pending, RideStatus::Accepted])) {
            $this->ride->update(['status' => RideStatus::Cancelled]);
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function finishRide() 
    {
        $this->ride->update(['status' => RideStatus::Finished]);
        $this->showSummary = true;
    }

    public function confirmPayment()
    {
        $this->ride->update([
            'status' => RideStatus::Completed,
            'payment_method' => $this->paymentMethod
        ]);

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function getDurationProperty()
    {
        return $this->ride->created_at->diffInMinutes(now());
    }
}; ?>

<div class="relative h-screen w-full overflow-hidden bg-gray-900" wire:poll.5s>
    
    @if($ride->status === \App\Enums\RideStatus::Cancelled)
        <div class="absolute inset-0 z-[100] bg-white flex flex-col items-center justify-center p-6 text-center">
            <span class="text-6xl mb-4">⚠️</span>
            <h2 class="text-2xl font-bold text-gray-900">Corrida Cancelada</h2>
            <p class="text-gray-500 mb-6">Esta viagem não está mais ativa.</p>
            <a href="{{ route('dashboard') }}" wire:navigate class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold">Voltar ao Início</a>
        </div>
    @endif

    @if(!$showSummary)
        {{-- ESTADO 4: CORRIDA EM ANDAMENTO --}}
        <div id="navigation-map" class="absolute inset-0 z-0" wire:ignore></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
            <div class="max-w-md mx-auto bg-white rounded-[2.5rem] p-8 shadow-2xl border-t-4 border-orange-500">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-1">Status atual</p>
                        <p class="text-xl font-black text-gray-900 uppercase italic">{{ $ride->status->label() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Valor</p>
                        <p class="text-xl font-black text-gray-900">R$ {{ number_format($ride->fare, 2, ',', '.') }}</p>
                    </div>
                </div>

                <div class="space-y-4 mb-6 py-4 border-y border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <p class="text-xs font-bold text-gray-500 truncate">{{ $ride->origin_address }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                        <p class="text-xs font-bold text-gray-900 truncate">{{ $ride->destination_address }}</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    {{-- Botão de Cancelar visível apenas se permitido (Requisito 1) --}}
                    @if(in_array($ride->status, [\App\Enums\RideStatus::Pending, \App\Enums\RideStatus::Accepted]))
                        <button wire:click="cancelRide" wire:confirm="Deseja cancelar esta corrida?" class="flex-1 py-4 border-2 border-red-100 text-red-500 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-red-50 transition-all">
                            Cancelar
                        </button>
                    @endif

                    {{-- Botão de simulação/finalização (Requisito 5) --}}
                    <button wire:click="finishRide" class="flex-[2] py-4 bg-gray-900 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg active:scale-95 transition-all">
                        @if($ride->status === \App\Enums\RideStatus::InProgress)
                            Finalizar Viagem
                        @else
                            Aguardando Início
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- ESTADO 5: RESUMO E PAGAMENTO --}}
        <div class="absolute inset-0 bg-gray-50 z-20 flex flex-col p-8 overflow-y-auto">
            {{-- Mantém o código original do resumo que você enviou, pois já está funcional --}}
            <div class="text-center mb-8 mt-4">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500 text-white rounded-[2rem] mb-6 text-4xl shadow-lg">
                    🏁
                </div>
                <h2 class="text-3xl font-black uppercase text-gray-900">Viagem Finalizada</h2>
                <p class="text-gray-400 font-bold text-[11px] tracking-widest mt-2">RESUMO DO TRAJETO</p>
            </div>

            <div class="max-w-sm mx-auto w-full space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-5 bg-white rounded-3xl shadow-sm text-center border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tempo</p>
                        <p class="text-2xl font-black text-gray-800">{{ $this->duration }} min</p>
                    </div>
                    <div class="p-5 bg-white rounded-3xl shadow-sm text-center border border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Distância</p>
                        <p class="text-2xl font-black text-gray-800">{{ number_format($ride->distance ?? 0, 1) }} km</p>
                    </div>
                </div>

                <div class="p-8 bg-white rounded-[2.5rem] border-2 border-green-100 text-center shadow-sm">
                    <p class="text-[11px] font-black text-green-600 uppercase mb-2">Total pago</p>
                    <p class="text-5xl font-black text-gray-900 tracking-tighter">R$ {{ number_format($ride->fare, 2, ',', '.') }}</p>
                </div>

                <div class="space-y-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase text-center tracking-widest">Método de Pagamento</p>
                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="$set('paymentMethod', 'money')" class="py-5 border-2 rounded-3xl font-black flex flex-col items-center transition-all {{ $paymentMethod == 'money' ? 'border-orange-500 bg-orange-50' : 'border-white bg-white' }}">
                            <span class="text-2xl mb-1">💵</span>
                            <span class="text-[10px]">DINHEIRO</span>
                        </button>
                        <button wire:click="$set('paymentMethod', 'pix')" class="py-5 border-2 rounded-3xl font-black flex flex-col items-center transition-all {{ $paymentMethod == 'pix' ? 'border-orange-500 bg-orange-50' : 'border-white bg-white' }}">
                            <span class="text-2xl mb-1">📱</span>
                            <span class="text-[10px]">PIX</span>
                        </button>
                    </div>
                </div>

                <button wire:click="confirmPayment" class="w-full py-6 bg-gray-900 text-white rounded-3xl font-black uppercase tracking-widest text-lg shadow-xl active:scale-95 transition-all">
                    Confirmar e Finalizar
                </button>
            </div>
        </div>
    @endif

    {{-- Script do Mapa --}}
    @if(!$showSummary)
    <script>
        document.addEventListener('livewire:navigated', () => {
            const mapContainer = document.getElementById('navigation-map');
            if(!mapContainer) return;

            const map = L.map('navigation-map', { zoomControl: false }).setView([{{ $ride->origin_lat }}, {{ $ride->origin_lng }}], 16);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);

            L.Routing.control({
                waypoints: [
                    L.latLng({{ $ride->origin_lat }}, {{ $ride->origin_lng }}),
                    L.latLng({{ $ride->destination_lat }}, {{ $ride->destination_lng }})
                ],
                lineOptions: { styles: [{ color: '#EA580C', weight: 8, opacity: 0.7 }] },
                createMarker: function() { return null; },
                addWaypoints: false,
                fitSelectedRoutes: true
            }).addTo(map);
        });
    </script>
    @endif
</div>