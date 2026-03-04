<x-app-layout>
    {{-- Agora $ride é um objeto da model Ride, não apenas uma string --}}
    <livewire:active-ride :ride="$ride" />
</x-app-layout>