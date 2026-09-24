<div class="flex items-start justify-between gap-4 rounded-soft bg-sunken px-4 py-3">
    <div>
        <p class="font-medium text-ink">{{ $this->client->name }}</p>
        <p class="text-sm text-ink-muted">
            {{ collect([$this->client->type->label(), $this->client->phone, $this->client->email])->filter()->implode(' · ') }}
        </p>
    </div>
    <button type="button" wire:click="clearClient" class="min-h-11 text-sm text-primary underline underline-offset-4">
        {{ __('appointments.create.change_client') }}
    </button>
</div>
