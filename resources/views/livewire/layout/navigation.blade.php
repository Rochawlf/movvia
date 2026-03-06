<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                        <x-application-logo class="block h-8 w-auto fill-current text-primary-600" />
                        <span class="font-black italic text-xl tracking-tighter text-gray-900">MOVVIA</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate class="font-bold uppercase text-[10px] tracking-widest">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-gray-500 bg-white hover:text-primary-600 transition ease-in-out duration-150 uppercase tracking-tighter italic">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- AJUSTADO: Nome da rota corrigido para profile.edit --}}
                        <x-dropdown-link :href="route('profile.edit')" wire:navigate>
                            {{ __('Meu Perfil') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('ride.history')" wire:navigate>
                            {{ __('Minhas Viagens') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-100"></div>

                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link class="text-red-600 font-bold uppercase text-[10px]">
                                {{ __('Sair do App') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-primary-600 hover:bg-gray-50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-50 shadow-inner">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate class="font-bold italic uppercase text-xs">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-100">
            <div class="px-4 flex items-center gap-3 mb-3">
                <div class="h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 font-black italic">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <div class="font-black italic text-sm text-gray-800 uppercase tracking-tighter" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-[10px] text-gray-400 uppercase tracking-widest">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="space-y-1">
                {{-- AJUSTADO: Nome da rota corrigido para profile.edit --}}
                <x-responsive-nav-link :href="route('profile.edit')" wire:navigate class="font-bold text-xs uppercase">
                    {{ __('Configurações de Perfil') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('ride.history')" wire:navigate class="font-bold text-xs uppercase">
                    {{ __('Histórico de Corridas') }}
                </x-responsive-nav-link>

                <div class="border-t border-gray-50 my-2"></div>

                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link class="text-red-600 font-black uppercase text-xs">
                        {{ __('Deslogar do Movvia') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>