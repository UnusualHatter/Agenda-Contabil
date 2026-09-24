<div>
    <x-input-label for="client_search" :value="__('appointments.fields.client')" class="sr-only" />
    <x-text-input id="client_search" type="search" wire:model.live.debounce.300ms="client_search" autocomplete="off"
                  :placeholder="__('appointments.create.search_placeholder')" class="block w-full" />
    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
</div>

@if (mb_strlen(trim($client_search)) >= 2)
    <ul class="-mx-2 space-y-1">
        @foreach ($this->clientResults as $result)
            <li wire:key="client-{{ $result->id }}">
                <button type="button" wire:click="selectClient({{ $result->id }})"
                        class="flex w-full flex-col rounded-soft px-3 py-2 text-start transition-colors hover:bg-sunken">
                    <span class="font-medium text-ink">{{ $result->name }}</span>
                    <span class="text-sm text-ink-muted">{{ collect([$result->type->label(), $result->phone, $result->email])->filter()->implode(' · ') }}</span>
                </button>
            </li>
        @endforeach
    </ul>

    @if ($this->clientResults->isEmpty())
        <p class="text-sm text-ink-muted">{{ __('appointments.create.no_results', ['term' => $client_search]) }}</p>
    @endif
@else
    <p class="text-sm text-ink-muted">{{ __('appointments.create.search_hint') }}</p>
@endif

@can('create', App\Models\Client::class)
    <x-secondary-button wire:click="startClientCreation">+ {{ __('appointments.create.create_client') }}</x-secondary-button>
@endcan
