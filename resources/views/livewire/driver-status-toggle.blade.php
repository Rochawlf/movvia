<button 
    wire:click="toggleStatus"
    class="relative inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors
        {{ $isOnline 
            ? 'bg-green-100 text-green-700 hover:bg-green-200' 
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200' 
        }}"
>
    <span class="flex items-center space-x-2">
        <span class="w-2 h-2 rounded-full {{ $isOnline ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
        <span>{{ $isOnline ? 'Online' : 'Offline' }}</span>
    </span>
</button>