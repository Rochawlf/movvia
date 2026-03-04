<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight uppercase tracking-tighter">
                Minhas Viagens
            </h2>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-orange-500 uppercase">Voltar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Chamamos o componente aqui --}}
            <livewire:ride-history />
        </div>
    </div>
</x-app-layout>