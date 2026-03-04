<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movvia - O seu app de mobilidade na Bahia</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans bg-white text-black">

    <nav class="fixed w-full z-50 bg-white border-b border-gray-100 py-4">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center gap-8">
                <a href="/">
                    <img src="{{ asset('img/logo_movvia.png') }}" alt="Movvia Logo" class="h-10 w-auto">
                </a>

                <div class="hidden md:flex gap-6 text-sm font-medium">
                    <a href="#viajar" class="hover:text-gray-600">Viajar</a>
                    <a href="#motorista" class="hover:text-gray-600">Dirigir</a>
                    <a href="#seguranca" class="hover:text-gray-600">Segurança</a>
                </div>
            </div>

            @if (Route::has('login'))
            <livewire:welcome.navigation />
            @endif
        </div>
    </nav>

    <main>
        <section id="viajar" class="pt-32 pb-20 bg-white">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2 space-y-8">
                    <div class="inline-block bg-black text-white px-3 py-1 rounded text-xs font-bold uppercase tracking-widest">
                        Bahia ➔ Brasil
                    </div>
                    <h1 class="text-6xl font-black leading-tight tracking-tight">
                        Vá a qualquer lugar com a Movvia
                    </h1>
                    <p class="text-gray-600 text-lg">O app que nasceu em Camaçari para conquistar o país. Tecnologia local, padrão internacional.</p>

                    <div class="flex flex-col gap-4 max-w-sm">
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xl">📍</span>
                            <input type="text" placeholder="Local de partida" class="w-full pl-12 pr-4 py-4 bg-gray-100 border-none rounded-lg focus:ring-2 focus:ring-black">
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xl">🏁</span>
                            <input type="text" placeholder="Informe o destino" class="w-full pl-12 pr-4 py-4 bg-gray-100 border-none rounded-lg focus:ring-2 focus:ring-black">
                        </div>
                        <a href="{{ route('register', ['role' => 'passenger']) }}" class="w-full py-4 bg-black text-white text-center rounded-lg font-bold text-lg hover:bg-gray-800 transition shadow-lg">
                            Ver preços
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <img src="https://e1.pxfuel.com/desktop-wallpaper/505/616/desktop-wallpaper-things-to-do-touring-alaskan-road-trip.jpg" alt="Travel" class="rounded-lg shadow-sm">
                </div>
            </div>
        </section>

        <div class="bg-gray-50 border-y border-gray-100 py-6 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <p class="text-center font-bold text-gray-400 uppercase tracking-[0.2em] text-sm italic">
                    Atuando em toda a <span class="text-black">Bahia</span>. Em breve, em todo o <span class="text-black">Brasil</span>.
                </p>
            </div>
        </div>

        <section id="seguranca" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <h2 class="text-4xl font-black mb-16 text-center">Tudo o que você precisa em um app</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="space-y-4">
                        <div class="text-4xl">🛡️</div>
                        <h3 class="text-xl font-bold">Segurança 24h</h3>
                        <p class="text-gray-600">Monitoramento em tempo real de todas as viagens e suporte direto para passageiros e motoristas.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="text-4xl">💰</div>
                        <h3 class="text-xl font-bold">Preço Justo</h3>
                        <p class="text-gray-600">Algoritmo otimizado para garantir a melhor tarifa para você e a melhor taxa para o motorista.</p>
                    </div>
                    <div class="space-y-4">
                        <div class="text-4xl">🤝</div>
                        <h3 class="text-xl font-bold">Apoio Local</h3>
                        <p class="text-gray-600">Diferente dos outros, nós estamos aqui. Atendimento humanizado e focado na nossa região.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="motorista" class="bg-[#FFD100] py-24">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center gap-16">
                <div class="md:w-1/2">
                    <div class="relative">
                        <img src="https://img.freepik.com/fotos-premium/motorista-usando-navegacao-gps-no-smartphone-para-viagens_51665-1216.jpg?semt=ais_hybrid&w=740&q=80" alt="Motorista Movvia" class="rounded-3xl shadow-2xl relative z-10">
                        <div class="absolute -bottom-6 -right-6 bg-white p-6 rounded-2xl shadow-xl z-20 hidden lg:block">
                            <p class="text-3xl font-black">R$ 250,00</p>
                            <p class="text-xs font-bold text-gray-400 uppercase">Média extra diária</p>
                        </div>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-6">
                    <h2 class="text-5xl font-black text-black leading-tight">
                        Quer fazer uma renda extra? Seja motorista da Movvia.
                    </h2>
                    <p class="text-xl text-black/80 font-medium">
                        Seja seu próprio chefe. Defina seus horários, ganhe por cada viagem e ajude a movimentar a Bahia com a gente.
                    </p>
                    <ul class="space-y-3 font-bold text-black/70">
                        <li>✅ Recebimento rápido</li>
                        <li>✅ Suporte exclusivo na Bahia</li>
                        <li>✅ Taxas mais baixas do mercado</li>
                    </ul>
                    <div class="pt-4">
                        <a href="{{ route('register', ['role' => 'driver']) }}" class="inline-block px-12 py-5 bg-black text-white rounded-xl font-black text-xl hover:scale-105 transition transform shadow-xl">
                            Cadastre-se para dirigir
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-20 bg-black text-white">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 border-b border-gray-800 pb-12">
            <div class="space-y-4 col-span-2">
                <img src="{{ asset('img/logo_movvia.png') }}" alt="Movvia Logo" class="h-12 w-auto brightness-0 invert">

                <p class="text-gray-400 text-sm max-w-sm">
                    O app de mobilidade que entende o povo baiano. <br>
                    Nascido em Camaçari, pronto para o Brasil.
                </p>
            </div>
            <div class="space-y-4">
                <h4 class="font-bold text-sm uppercase text-gray-500">Links Úteis</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="#" class="hover:text-white">Central de Ajuda</a></li>
                    <li><a href="#" class="hover:text-white">Cidades Atendidas</a></li>
                    <li><a href="#" class="hover:text-white">Termos de Uso</a></li>
                </ul>
            </div>
            <div class="space-y-4">
                <h4 class="font-bold text-sm uppercase text-gray-500">Redes Sociais</h4>
                <ul class="flex gap-4">
                    <li class="bg-gray-800 w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#FFD100] hover:text-black transition cursor-pointer font-bold">IG</li>
                    <li class="bg-gray-800 w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#FFD100] hover:text-black transition cursor-pointer font-bold">FB</li>
                    <li class="bg-gray-800 w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#FFD100] hover:text-black transition cursor-pointer font-bold">LK</li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 pt-8 text-[10px] text-gray-600 flex flex-col md:flex-row justify-between items-center gap-4 uppercase tracking-widest">
            <p>&copy; {{ date('Y') }} Movvia Tecnologia S.A. Todos os direitos reservados.</p>
            <p>Criado com orgulho na Bahia | Laravel v{{ Illuminate\Foundation\Application::VERSION }}</p>
        </div>
    </footer>
</body>

</html>