<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DriverStatusToggle extends Component
{
    public $isOnline = false;

    public function mount()
    {
        $this->isOnline = Auth::user()->is_online ?? false;
    }

    public function toggleStatus()
    {
        $user = Auth::user();
        $this->isOnline = !$this->isOnline;
        
        $user->update(['is_online' => $this->isOnline]);
        
        $this->dispatch('status-changed', online: $this->isOnline);
    }

    public function render()
    {
        return view('livewire.driver-status-toggle');
    }
}