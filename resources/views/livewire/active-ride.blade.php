<?php
use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Carbon\Carbon;

new class extends Component {
    public Ride $ride;
    public bool $showSummary = false;
    public string $paymentMethod = 'money'; // Padrão dinheiro

    public function finishRide() 
    {
        // Atualiza o status no banco para o passageiro saber que chegou
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
        // Calcula a diferença entre a criação (ou início) e agora
        return $this->ride->created_at->diffInMinutes(now());
    }
}; ?>

<div class="relative h-screen w-full overflow-hidden bg-gray-900">
    
    @if(!$showSummary)
        {{-- NAVEGAÇÃO ATIVA --}}
        <div id="navigation-map" class="absolute inset-0 z-0" wire:ignore></div>
        
        <div class="absolute bottom-0 left-0 right-0 p-6 z-10 pointer-events-none">
            <div class="max-w-md mx-auto bg-white/95 backdrop-blur-xl rounded-[2.5rem] p-8 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] border-t-4 border-orange-500 pointer-events-auto transition-transform">
                <div class="mb-5">
                    <p class="text-[11px] font-black text-orange-500 uppercase tracking-widest mb-1">Destino</p>
                    <p class="text-lg font-black text-gray-900 truncate">{{ $ride->destination_address }}</p>
                </div>
                <button wire:click="finishRide" class="w-full py-5 bg-gradient-to-r from-gray-900 to-black text-white rounded-[1.5rem] font-black uppercase text-xl shadow-[0_15px_30px_-10px_rgba(0,0,0,0.4)] hover:shadow-[0_20px_40px_-5px_rgba(0,0,0,0.5)] active:scale-95 transition-all duration-300">
                    Chegamos ao Destino
                </button>
            </div>
        </div>
    @else
        {{-- RESUMO DA FINALIZAÇÃO --}}
        <div class="absolute inset-0 bg-gray-50 z-20 flex flex-col p-8 overflow-y-auto">
            <div class="text-center mb-8 mt-4">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-400 to-green-500 text-white rounded-[2.5rem] mb-6 text-5xl shadow-[0_15px_30px_-10px_rgba(34,197,94,0.5)] rotate-12">
                    <span class="-rotate-12">🏁</span>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tighter text-gray-900 inline-block drop-shadow-sm">Corrida Finalizada</h2>
                <p class="text-gray-400 font-bold uppercase text-[11px] tracking-widest mt-2">Resumo do trajeto finalizado</p>
            </div>

            <div class="space-y-6 flex-1 max-w-sm mx-auto w-full">
                {{-- DADOS DA VIAGEM --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-5 bg-white rounded-[2rem] border border-gray-100 shadow-sm text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tempo</p>
                        <p class="text-2xl font-black text-gray-800">{{ $this->duration }} min</p>
                    </div>
                    <div class="p-5 bg-white rounded-[2rem] border border-gray-100 shadow-sm text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Distância</p>
                        <p class="text-2xl font-black text-gray-800">{{ number_format($ride->distance_km ?? 0, 1) }} km</p>
                    </div>
                </div>

                <div class="p-8 bg-gradient-to-br from-white to-green-50/30 rounded-[2.5rem] border border-green-100 text-center shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-green-500"></div>
                    <p class="text-[11px] font-black text-green-600 uppercase mb-2 tracking-widest">Valor Total a Receber</p>
                    <p class="text-5xl font-black text-gray-900 tracking-tighter drop-shadow-md">R$ {{ number_format($ride->fare, 2, ',', '.') }}</p>
                    <div class="mt-4 inline-block bg-green-100/50 px-4 py-2 rounded-full">
                        <p class="text-[11px] font-bold text-green-700 tracking-wide">* Seu ganho: <span class="font-black">R$ {{ number_format($ride->fare * 0.9, 2, ',', '.') }}</span></p>
                    </div>
                </div>

                {{-- SELEÇÃO DE PAGAMENTO --}}
                <div class="space-y-4">
                    <p class="text-[11px] font-black text-gray-400 uppercase text-center tracking-widest">Forma de Pagamento</p>
                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="$set('paymentMethod', 'money')" class="py-5 border-2 rounded-[2rem] font-black flex flex-col items-center transition-all duration-300 {{ $paymentMethod == 'money' ? 'border-orange-500 bg-orange-50/50 shadow-[0_10px_20px_-10px_rgba(249,115,22,0.3)] scale-[1.02]' : 'border-white bg-white shadow-sm hover:shadow-md' }}">
                            <span class="text-3xl drop-shadow-sm mb-2">💵</span>
                            <span class="text-[11px] tracking-widest text-gray-700">DINHEIRO</span>
                        </button>
                        <button wire:click="$set('paymentMethod', 'pix')" class="py-5 border-2 rounded-[2rem] font-black flex flex-col items-center transition-all duration-300 {{ $paymentMethod == 'pix' ? 'border-orange-500 bg-orange-50/50 shadow-[0_10px_20px_-10px_rgba(249,115,22,0.3)] scale-[1.02]' : 'border-white bg-white shadow-sm hover:shadow-md' }}">
                            <span class="text-3xl drop-shadow-sm mb-2">📱</span>
                            <span class="text-[11px] tracking-widest text-gray-700">PIX</span>
                        </button>
                    </div>
                </div>
            </div>

            <button wire:click="confirmPayment" class="mt-8 mb-4 max-w-sm mx-auto w-full py-6 bg-gradient-to-r from-gray-900 to-black text-white rounded-[2rem] font-black uppercase tracking-widest text-lg shadow-[0_15px_30px_-10px_rgba(0,0,0,0.5)] hover:shadow-[0_20px_40px_-5px_rgba(0,0,0,0.6)] active:scale-95 transition-all duration-300">
                Confirmar e Receber
            </button>
        </div>
    @endif

    {{-- Script do Mapa (só roda se o resumo não estiver aberto) --}}
    @if(!$showSummary)
    <script>
        document.addEventListener('livewire:navigated', () => {
            const map = L.map('navigation-map', { zoomControl: false }).setView([{{ $ride->origin_lat }}, {{ $ride->origin_lng }}], 16);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);

            L.Routing.control({
                waypoints: [
                    L.latLng({{ $ride->origin_lat }}, {{ $ride->origin_lng }}),
                    L.latLng({{ $ride->destination_lat }}, {{ $ride->destination_lng }})
                ],
                lineOptions: { styles: [{ color: '#EA580C', weight: 8 }] },
                createMarker: function() { return null; },
                addWaypoints: false
            }).addTo(map);
        });
    </script>
    @endif
</div>