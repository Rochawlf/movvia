<?php

use Livewire\Volt\Component;
use App\Models\Ride;
use App\Enums\RideStatus;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public bool $isOnline = false;
    public $activeRideRequest = null;

    public function mount()
    {
        // Puxa o status real do banco de dados ao carregar
        $this->isOnline = (bool) Auth::user()->is_online;
    }

    // No PHP do seu componente
    public function toggleStatus()
    {
        $user = Auth::user();
        $user->update(['is_online' => !$user->is_online]);
        $this->isOnline = (bool) $user->is_online;

        // Dispara o som correspondente
        $this->dispatch($this->isOnline ? 'play-online' : 'play-offline');

        if (!$this->isOnline) $this->activeRideRequest = null;
    }

    public function checkNewRides()
    {
        if ($this->isOnline) {
            $lastRideId = $this->activeRideRequest?->id;

            $this->activeRideRequest = Ride::where('status', RideStatus::Pending)->latest()->first();

            // Se surgiu uma corrida nova que não estava na tela antes
            if ($this->activeRideRequest && $this->activeRideRequest->id !== $lastRideId) {
                $this->dispatch('play-new-ride');

                // Voz de GPS: "Nova corrida disponível para " + endereço
                $this->dispatch('speak', text: "Nova corrida para " . $this->activeRideRequest->destination_address);
            }
        }
    }

    public function acceptRide($rideId)
    {
        $ride = Ride::find($rideId);

        if ($ride && $ride->status === RideStatus::Pending) {
            // 1. Para o som imediatamente no navegador
            $this->dispatch('stop-new-ride-sound');

            // 2. Atualiza o banco
            $ride->update([
                'driver_id' => auth()->id(),
                'status' => RideStatus::Accepted
            ]);

            // 3. Redireciona
            return $this->redirect(route('ride.active', $ride->id), navigate: true);
        }
    }
}; ?>

<div class="relative h-screen w-full overflow-hidden" wire:poll.5s="checkNewRides">
    {{-- MAPA FULLSCREEN --}}
    <div id="driver-map" class="absolute inset-0 z-0 bg-gray-200" wire:ignore></div>

    {{-- BARRA DE STATUS SUPERIOR --}}
    <div class="absolute top-6 left-0 right-0 px-4 z-10">
        <div class="max-w-md mx-auto bg-black text-white rounded-full p-4 flex justify-between items-center shadow-2xl">
            <div class="flex items-center gap-2 ml-2">
                <div class="h-2 w-2 rounded-full {{ $isOnline ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></div>
                <span class="text-[10px] font-black uppercase tracking-widest">
                    {{ $isOnline ? 'Procurando Viagens' : 'Você está offline' }}
                </span>
            </div>
            <div class="font-black text-lg">R$ {{ number_format(Auth::user()->daily_earnings ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- BOTÃO CONECTAR (ESTILO UBER/99) --}}
    <div class="absolute bottom-10 left-0 right-0 px-8 z-10 flex flex-col items-center">
        @if(!$activeRideRequest)
        <button wire:click="toggleStatus"
            class="w-full max-w-xs py-5 rounded-full font-black uppercase text-xl transition-all active:scale-95 shadow-2xl {{ $isOnline ? 'bg-white text-red-600 border-2 border-red-100' : 'bg-[#FFD100] text-black' }}">
            {{ $isOnline ? 'Ficar Offline' : 'Conectar' }}
        </button>
        @endif
    </div>

    {{-- POP-UP DE CHAMADA --}}
    @if($activeRideRequest)
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm z-20 flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white rounded-[3rem] p-8 shadow-2xl border-4 border-[#FFD100] animate-bounce-short">
            <div class="text-center mb-6">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nova Viagem Disponível</p>
                <p class="text-5xl font-black text-green-600 mt-2">R$ {{ number_format($activeRideRequest->fare * 0.9, 2, ',', '.') }}</p>
            </div>

            <div class="space-y-4 mb-8">
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-3xl">
                    <div class="text-3xl">📍</div>
                    <div>
                        <p class="text-[10px] font-black text-orange-500 uppercase">Partida</p>
                        <p class="font-bold text-sm truncate">{{ $activeRideRequest->origin_address }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-3xl">
                    <div class="text-3xl">🏁</div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase">Destino</p>
                        <p class="font-bold text-sm truncate">{{ $activeRideRequest->destination_address }}</p>
                    </div>
                </div>
            </div>

            <button wire:click="acceptRide({{ $activeRideRequest->id }})" class="w-full py-6 bg-black text-white rounded-3xl font-black uppercase text-2xl shadow-xl hover:bg-gray-900 transition">
                ACEITAR
            </button>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:navigated', () => {
            const map = L.map('driver-map', {
                zoomControl: false,
                attributionControl: false
            }).setView([-12.6975, -38.3242], 16);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(map);

            if (navigator.geolocation) {
                navigator.geolocation.watchPosition((pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.panTo([lat, lng]);

                    // Atualiza a posição no mapa com um ícone azul de seta
                    L.circleMarker([lat, lng], {
                        radius: 8,
                        color: '#3b82f6',
                        fillOpacity: 1
                    }).addTo(map);
                });
            }
        });

    const soundOnline = new Audio('/assets/sounds/online.mp3');
    const soundOffline = new Audio('/assets/sounds/offline.mp3');
    const soundNewRide = new Audio('/assets/sounds/new-ride.mp3');
    const soundCancel = new Audio('/assets/sounds/cancel.mp3');

    soundNewRide.loop = true;

    // Função mestre para parar a sirene
    const stopSirene = () => {
        soundNewRide.pause();
        soundNewRide.currentTime = 0;
    };

    window.addEventListener('play-online', () => {
        soundOnline.currentTime = 0;
        soundOnline.play();
        setTimeout(() => { soundOnline.pause(); }, 1000);
    });

    window.addEventListener('play-offline', () => {
        stopSirene();
        soundOffline.currentTime = 0;
        soundOffline.play();
    });

    window.addEventListener('play-new-ride', () => {
        soundNewRide.currentTime = 0;
        soundNewRide.play();
    });

    // Esse aqui é o que o acceptRide chama
    window.addEventListener('stop-new-ride-sound', () => {
        stopSirene();
    });

    // SEGURANÇA: Se o motorista mudar de página, para o som na marra
    document.addEventListener('livewire:navigating', () => {
        stopSirene();
    });
</script>
    </script>

    // --- VOZ DE GPS (Web Speech API) ---
    window.addEventListener('speak', event => {
    const text = event.detail.text;
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'pt-BR';
    utterance.rate = 0.9; // Velocidade um pouco mais lenta para ser clara
    window.speechSynthesis.speak(utterance);
    });
    </script>
</div>