<?php

declare(strict_types=1);

namespace App\Livewire\Clients;

use App\Domain\Clients\Queries\SearchClients;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    #[Url(as: 'busca', except: '')]
    public string $search = '';

    #[Url(as: 'tipo', except: '')]
    public string $type = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.clients.client-index', [
            'clients' => SearchClients::query($this->search)
                ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
                ->withCount('appointments')
                ->with([
                    'nextAppointment' => fn ($query) => $query->with(['service', 'responsible']),
                    'recentAppointments' => fn ($query) => $query->with('service'),
                ])
                ->simplePaginate(15),
        ]);
    }
}
