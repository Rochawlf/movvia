<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- VISÃO DO PASSAGEIRO --}}
            @if(auth()->user()->role === \App\Enums\UserRole::Passenger)
            <livewire:ride-status />
            <livewire:request-ride />
            @endif
            {{-- Dentro da área do Passageiro no seu dashboard.blade.php --}}
            @if(auth()->user()->role === 'passenger')
            <div class="space-y-6">
                {{-- 1. O Rastreador (só aparece se houver corrida ativa) --}}
                <livewire:passenger-ride-status />

                {{-- 2. O formulário de pedido (você pode esconder se quiser quando houver corrida) --}}
                <livewire:request-ride />
            </div>
            @endif

            {{-- VISÃO DO MOTORISTA --}}
            @if(auth()->user()->role === \App\Enums\UserRole::Driver)
            <livewire:active-trip />
            <livewire:available-rides />
            @endif

        </div>
    </div>


</x-app-layout>