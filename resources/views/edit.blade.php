<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Meu Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- Mensagem de sucesso --}}
                    @if (session('status') === 'profile-updated')
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            Perfil atualizado com sucesso!
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        {{-- Nome --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" name="name" id="name" 
                                value="{{ old('name', auth()->user()->name) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                            <input type="email" name="email" id="email" 
                                value="{{ old('email', auth()->user()->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Telefone (adicionei pois é comum em apps de mobilidade) --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                            <input type="tel" name="phone" id="phone" 
                                value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                placeholder="(00) 00000-0000"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipo de usuário (apenas visualização) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de conta</label>
                            <div class="mt-1 p-2 bg-gray-50 rounded-md">
                                @if(auth()->user()->role === 'passenger')
                                    <span class="text-sm text-gray-600">Passageiro</span>
                                @elseif(auth()->user()->role === 'driver')
                                    <span class="text-sm text-gray-600">Motorista</span>
                                @endif
                            </div>
                        </div>

                        {{-- Botões --}}
                        <div class="flex items-center gap-4">
                            <button type="submit" 
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                                Salvar alterações
                            </button>

                            @if(auth()->user()->role === 'driver')
                                <a href="{{ route('profile.documents') }}" 
                                   class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                                    Meus documentos
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Seção de exclusão de conta (opcional) --}}
                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-red-600">Zona de Perigo</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Uma vez excluída, sua conta não poderá ser recuperada.
                        </p>

                        <form method="POST" action="{{ route('profile.destroy') }}" 
                              onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível.')"
                              class="mt-4">
                            @csrf
                            @method('DELETE')
                            
                            <div class="mb-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Confirme sua senha
                                </label>
                                <input type="password" name="password" id="password" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            </div>

                            <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                Excluir minha conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>