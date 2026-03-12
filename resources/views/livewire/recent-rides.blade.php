<div class="space-y-3">
    @forelse($rides as $ride)
        <button
            @click="$dispatch('ride-selected-from-history', [{ lat: {{ (float) $ride->destination_lat }}, lng: {{ (float) $ride->destination_lng }}, address: '{{ addslashes($ride->destination_address) }}' }])"
            type="button"
            class="w-full text-left flex items-center justify-between gap-3 rounded-[1.4rem] border border-white/10 bg-white/5 hover:bg-white/8 transition-all px-4 py-4 active:scale-[0.98]"
        >
            <div class="flex-1 min-w-0 pr-2">
                <p class="font-black text-white text-sm flex items-center gap-2 leading-tight">
                    <span class="text-[10px] text-blue-300 shrink-0">📍</span>
                    <span class="truncate">{{ Str::limit($ride->origin_address, 18) }}</span>
                    <span class="text-orange-400 shrink-0">→</span>
                    <span class="truncate">{{ Str::limit($ride->destination_address, 18) }}</span>
                </p>

                <p class="text-[10px] font-black text-white/40 mt-2 uppercase tracking-[0.18em]">
                    {{ $ride->created_at->diffForHumans() }}
                </p>
            </div>

            <div class="text-right flex flex-col items-end gap-2 shrink-0">
                <p class="font-black text-white tracking-tight bg-gradient-to-r from-orange-500/15 to-blue-600/15 border border-white/10 px-3 py-1.5 rounded-xl">
                    R$ {{ number_format($ride->fare, 2, ',', '.') }}
                </p>

                @php
                    $statusColor = match($ride->status->value) {
                        'completed' => 'text-emerald-300 bg-emerald-500/10 border border-emerald-400/20',
                        'cancelled' => 'text-red-300 bg-red-500/10 border border-red-400/20',
                        'in_progress' => 'text-blue-300 bg-blue-500/10 border border-blue-400/20',
                        'accepted' => 'text-orange-300 bg-orange-500/10 border border-orange-400/20',
                        'pending' => 'text-yellow-200 bg-yellow-500/10 border border-yellow-300/20',
                        default => 'text-white/50 bg-white/5 border border-white/10',
                    };
                @endphp

                <span class="inline-block text-[8px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full {{ $statusColor }}">
                    {{ ucfirst(str_replace('_', ' ', $ride->status->value)) }}
                </span>
            </div>
        </button>
    @empty
        <div class="flex flex-col items-center justify-center py-10 rounded-[1.6rem] border border-white/10 bg-white/5">
            <span class="text-4xl mb-3 opacity-70">🏁</span>
            <p class="text-[10px] font-black text-white/45 uppercase tracking-[0.2em]">
                Nenhuma corrida recente
            </p>
        </div>
    @endforelse
</div>