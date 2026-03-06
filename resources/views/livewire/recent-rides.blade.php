<div class="space-y-4">
    @forelse($rides as $ride)
        <button @click="$dispatch('ride-selected-from-history', [{ lat: {{ (float) $ride->destination_lat }}, lng: {{ (float) $ride->destination_lng }}, address: '{{ addslashes($ride->destination_address) }}' }])" type="button" class="w-full text-left flex items-center justify-between text-sm border-b border-gray-50 pb-3 last:border-0 last:pb-0 hover:bg-gray-100 transition-colors p-2 -mx-2 rounded-xl active:scale-[0.98]">
            <div class="flex-1 pr-4">
                {{-- Trajeto com ícones simples para mobile --}}
                <p class="font-bold text-gray-900 flex items-center gap-1">
                    <span class="text-[10px] text-gray-400">📍</span>
                    {{ Str::limit($ride->origin_address, 18) }} 
                    <span class="text-orange-500">→</span> 
                    {{ Str::limit($ride->destination_address, 18) }}
                </p>
                <p class="text-[10px] font-medium text-gray-400 mt-1 uppercase tracking-wider">
                    {{ $ride->created_at->diffForHumans() }}
                </p>
            </div>

            <div class="text-right flex flex-col items-end gap-1">
                <p class="font-black text-gray-900 tracking-tighter bg-gray-100 px-2 py-1 rounded-lg">
                    R$ {{ number_format($ride->fare, 2, ',', '.') }}
                </p>
                
                {{-- Lógica de cores baseada no VALUE do Enum --}}
                @php
                    $statusColor = match($ride->status->value) {
                        'completed' => 'text-green-600 bg-green-50 border border-green-200',
                        'cancelled' => 'text-red-600 bg-red-50 border border-red-200',
                        'in_progress' => 'text-blue-600 bg-blue-50 border border-blue-200',
                        default => 'text-gray-400 bg-gray-50 border border-gray-200',
                    };
                @endphp

                <span class="inline-block text-[8px] font-black uppercase px-2 py-0.5 rounded-full mt-1 {{ $statusColor }}">
                    {{ ucfirst($ride->status->value) }}
                </span>
            </div>
        </button>
    @empty
        <div class="flex flex-col items-center justify-center py-8 opacity-30">
            <span class="text-4xl mb-2">🏁</span>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Nenhuma corrida recente</p>
        </div>
    @endforelse
</div>