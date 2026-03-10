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
        $this->isOnline = (bool) Auth::user()->is_online;
        $this->dispatch('driver-status-updated', isOnline: $this->isOnline);

    }

    public function toggleStatus()
    {
        $user = Auth::user();
        $user->update(['is_online' => !$user->is_online]);
        $this->isOnline = (bool) $user->is_online;
        $this->dispatch('driver-status-updated', isOnline: $this->isOnline);

        $this->dispatch($this->isOnline ? 'play-online' : 'play-offline');

        if (!$this->isOnline) {
            $this->activeRideRequest = null;
        }
    }

    public function checkNewRides()
    {
        $this->isOnline = (bool) Auth::user()->fresh()->is_online;
        $this->dispatch('driver-status-updated', isOnline: $this->isOnline);

        if (!$this->isOnline) {
            $this->activeRideRequest = null;
            return;
        }

        $lastRideId = $this->activeRideRequest?->id;

        $this->activeRideRequest = Ride::where('status', RideStatus::Pending)
            ->latest()
            ->first();

        if ($this->activeRideRequest && $this->activeRideRequest->id !== $lastRideId) {
            $this->dispatch('play-new-ride');
            $this->dispatch('speak', text: 'Nova corrida para ' . $this->activeRideRequest->destination_address);
        }
    }

    public function acceptRide($rideId)
    {
        $ride = Ride::find($rideId);

        if ($ride && $ride->status === RideStatus::Pending) {
            $this->dispatch('stop-new-ride-sound');

            $ride->update([
                'driver_id' => auth()->id(),
                'status' => RideStatus::Accepted,
            ]);

            return $this->redirect(route('ride.active', $ride->id), navigate: true);
        }
    }
};

?>

<div wire:poll.5s="checkNewRides">

    {{-- MODAL DE NOVA CORRIDA --}}
    @if($activeRideRequest)
        <div class="absolute inset-0 bg-black/45 backdrop-blur-sm z-[190] flex items-center justify-center p-5">
            <div
                class="w-full max-w-md bg-white rounded-[2.5rem] p-6 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.65)] border border-orange-100 relative overflow-hidden">

                <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-orange-500 via-orange-400 to-blue-600">
                </div>
                <div class="text-center mb-6 mt-2">
                    <p class="text-[11px] font-black text-orange-500 uppercase tracking-[0.25em]">
                        Nova viagem disponível
                    </p>

                    <p class="text-4xl font-black text-gray-900 tracking-tight mt-3">
                        R$ {{ number_format($activeRideRequest->fare * 0.9, 2, ',', '.') }}
                    </p>

                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-1">
                        Bruto: R$ {{ number_format($activeRideRequest->fare, 2, ',', '.') }}
                    </p>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-[1.5rem] border border-gray-100">
                        <div class="text-2xl shrink-0">📍</div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Partida</p>
                            <p class="font-bold text-sm text-gray-800 truncate">
                                {{ $activeRideRequest->origin_address }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-[1.5rem] border border-gray-100">
                        <div class="text-2xl shrink-0">🏁</div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Destino</p>
                            <p class="font-bold text-sm text-gray-800 truncate">
                                {{ $activeRideRequest->destination_address }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <button wire:click="acceptRide({{ $activeRideRequest->id }})"
                        class="w-full py-5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-[1.5rem] font-black uppercase tracking-widest text-lg shadow-[0_15px_30px_-10px_rgba(37,99,235,0.35)] active:scale-95 transition-all">
                        Aceitar corrida
                    </button>
                </div>
            </div>
        </div>
    @endif

    @once
        @push('scripts')
            <script>
                (() => {
                    if (window.movviaDriverAudioInitialized) return;
                    window.movviaDriverAudioInitialized = true;

                    const soundOnline = new Audio('/assets/sounds/online.mp3');
                    const soundOffline = new Audio('/assets/sounds/offline.mp3');
                    const soundNewRide = new Audio('/assets/sounds/new-ride.mp3');

                    soundNewRide.loop = true;

                    const stopSirene = () => {
                        soundNewRide.pause();
                        soundNewRide.currentTime = 0;
                    };

                    window.addEventListener('play-online', () => {
                        soundOnline.currentTime = 0;
                        soundOnline.play().catch(() => { });
                        setTimeout(() => {
                            soundOnline.pause();
                        }, 1000);
                    });

                    window.addEventListener('play-offline', () => {
                        stopSirene();
                        soundOffline.currentTime = 0;
                        soundOffline.play().catch(() => { });
                    });

                    window.addEventListener('play-new-ride', () => {
                        soundNewRide.currentTime = 0;
                        soundNewRide.play().catch(() => { });
                    });

                    window.addEventListener('stop-new-ride-sound', () => {
                        stopSirene();
                    });

                    document.addEventListener('livewire:navigating', () => {
                        stopSirene();
                    });

                    window.addEventListener('speak', (event) => {
                        const text = event.detail?.text;
                        if (!text) return;

                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = 'pt-BR';
                        utterance.rate = 0.9;
                        window.speechSynthesis.speak(utterance);
                    });
                })();
            </script>
        @endpush
    @endonce
</div>