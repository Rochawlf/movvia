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
    <div class="absolute top-6 left-0 right-0 px-4 z-10 transition-all duration-500">
        <div class="max-w-md mx-auto bg-gray-900/95 backdrop-blur-md text-white rounded-full p-4 px-6 flex justify-between items-center shadow-[0_15px_30px_-10px_rgba(0,0,0,0.5)] border border-gray-700">
            <div class="flex items-center gap-3">
                <div class="relative flex h-3 w-3">
                  @if($isOnline)
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  @endif
                  <span class="relative inline-flex rounded-full h-3 w-3 {{ $isOnline ? 'bg-green-500' : 'bg-red-500' }}"></span>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-200">
                    {{ $isOnline ? 'Procurando Viagens' : 'Você está offline' }}
                </span>
            </div>
            <div class="font-black text-lg drop-shadow-md">R$ {{ number_format(Auth::user()->daily_earnings ?? 0, 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- BOTÃO CONECTAR --}}
    <div class="absolute bottom-10 left-0 right-0 px-8 z-10 flex flex-col items-center">
        @if(!$activeRideRequest)
        <button wire:click="toggleStatus"
            class="w-full max-w-xs py-5 rounded-full font-black uppercase text-xl transition-all duration-300 active:scale-95 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] border-4 border-transparent {{ $isOnline ? 'bg-white text-red-600 border-white hover:bg-gray-50 hover:text-red-700' : 'bg-gradient-to-r from-orange-400 to-orange-500 text-white shadow-[0_20px_40px_-15px_rgba(249,115,22,0.6)] hover:shadow-[0_25px_50px_-15px_rgba(249,115,22,0.7)]' }}">
            {{ $isOnline ? 'Ficar Offline' : 'Conectar' }}
        </button>
        @endif
    </div>

    {{-- POP-UP DE CHAMADA --}}
    @if($activeRideRequest)
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md z-20 flex items-center justify-center p-6 transition-all duration-300">
        <div class="w-full max-w-md bg-white rounded-[3rem] p-8 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.8)] border-2 border-orange-500 animate-bounce-short relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-orange-400 to-orange-600"></div>
            <div class="text-center mb-8 mt-2">
                <p class="text-[11px] font-black text-orange-500 uppercase tracking-widest animate-pulse">Nova Viagem Disponível</p>
                <p class="text-5xl font-black text-gray-900 tracking-tighter mt-3 drop-shadow-sm">R$ {{ number_format($activeRideRequest->fare * 0.9, 2, ',', '.') }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase mt-1">Bruto: R$ {{ number_format($activeRideRequest->fare, 2, ',', '.') }}</p>
            </div>

            <div class="space-y-4 mb-8">
                <div class="flex items-center gap-4 p-5 bg-gray-50 rounded-[2rem] border border-gray-100 shadow-inner">
                    <div class="text-3xl drop-shadow-md">📍</div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Partida</p>
                        <p class="font-bold text-sm text-gray-800 truncate">{{ $activeRideRequest->origin_address }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5 bg-gray-50 rounded-[2rem] border border-gray-100 shadow-inner">
                    <div class="text-3xl drop-shadow-md">🏁</div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Destino</p>
                        <p class="font-bold text-sm text-gray-800 truncate">{{ $activeRideRequest->destination_address }}</p>
                    </div>
                </div>
            </div>

            <button wire:click="acceptRide({{ $activeRideRequest->id }})" class="w-full py-6 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-3xl font-black uppercase tracking-widest text-2xl shadow-[0_15px_30px_-10px_rgba(34,197,94,0.5)] hover:shadow-[0_20px_40px_-10px_rgba(34,197,94,0.6)] active:scale-95 transition-all duration-300">
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