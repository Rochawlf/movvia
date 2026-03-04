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
        
        <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
            <div class="max-w-md mx-auto bg-white rounded-[2.5rem] p-8 shadow-2xl border-t-4 border-[#FFD100]">
                <div class="mb-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase">Destino</p>
                    <p class="text-lg font-black text-gray-900 truncate">{{ $ride->destination_address }}</p>
                </div>
                <button wire:click="finishRide" class="w-full py-5 bg-black text-white rounded-2xl font-black uppercase text-xl shadow-xl active:scale-95 transition">
                    Chegamos ao Destino
                </button>
            </div>
        </div>
    @else
        {{-- RESUMO DA FINALIZAÇÃO --}}
        <div class="absolute inset-0 bg-white z-20 flex flex-col p-8 overflow-y-auto">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-full mb-4 text-4xl">
                    🏁
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tighter">Corrida Finalizada</h2>
                <p class="text-gray-400 font-bold uppercase text-[10px]">Resumo do trajeto em Camaçari</p>
            </div>

            <div class="space-y-6 flex-1">
                {{-- DADOS DA VIAGEM --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-3xl border border-gray-100 text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Tempo</p>
                        <p class="text-2xl font-black text-gray-900">{{ $this->duration }} min</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-3xl border border-gray-100 text-center">
                        <p class="text-[10px] font-black text-gray-400 uppercase">Distância</p>
                        <p class="text-2xl font-black text-gray-900">{{ number_format($ride->distance_km ?? 0, 1) }} km</p>
                    </div>
                </div>

                <div class="p-6 bg-green-50 rounded-[2rem] border-2 border-green-200 text-center">
                    <p class="text-xs font-black text-green-700 uppercase mb-1">Valor Total a Receber</p>
                    <p class="text-5xl font-black text-green-600 tracking-tighter">R$ {{ number_format($ride->fare, 2, ',', '.') }}</p>
                    <p class="text-[10px] font-bold text-green-800 mt-2 italic">* Seu ganho: R$ {{ number_format($ride->fare * 0.9, 2, ',', '.') }}</p>
                </div>

                {{-- SELEÇÃO DE PAGAMENTO --}}
                <div class="space-y-3">
                    <p class="text-xs font-black text-gray-400 uppercase text-center">Forma de Pagamento</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="$set('paymentMethod', 'money')" class="py-4 border-2 rounded-2xl font-black flex flex-col items-center transition {{ $paymentMethod == 'money' ? 'border-orange-500 bg-orange-50' : 'border-gray-100' }}">
                            <span class="text-2xl">💵</span>
                            <span class="text-[10px] mt-1">DINHEIRO</span>
                        </button>
                        <button wire:click="$set('paymentMethod', 'pix')" class="py-4 border-2 rounded-2xl font-black flex flex-col items-center transition {{ $paymentMethod == 'pix' ? 'border-orange-500 bg-orange-50' : 'border-gray-100' }}">
                            <span class="text-2xl">📱</span>
                            <span class="text-[10px] mt-1">PIX</span>
                        </button>
                    </div>
                </div>
            </div>

            <button wire:click="confirmPayment" class="mt-8 w-full py-5 bg-black text-white rounded-2xl font-black uppercase text-xl shadow-xl active:scale-95 transition">
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