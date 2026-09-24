<?php

declare(strict_types=1);

namespace App\Livewire\Clients;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Illuminate\View\View;
use Livewire\Component;

class ClientEditor extends Component
{
    public ClientForm $form;

    public function mount(?Client $client = null): void
    {
        if ($client?->exists) {
            $this->form->setClient($client);
        }
    }

    public function save(): void
    {
        $existing = $this->form->client;

        $this->authorize($existing ? 'update' : 'create', $existing ?? Client::class);

        $client = $this->form->save();

        session()->flash('notice', __($existing ? 'clients.flash.updated' : 'clients.flash.created'));

        $this->redirectRoute('clients.show', $client);
    }

    public function render(): View
    {
        return view('livewire.clients.client-editor');
    }
}
