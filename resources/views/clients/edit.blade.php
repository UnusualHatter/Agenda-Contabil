<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-8 px-4 py-10 sm:px-6 lg:px-8">
        <x-page-header :title="__('clients.edit_title')" :subtitle="$client->name" />

        <livewire:clients.client-editor :client="$client" />
    </div>
</x-app-layout>
