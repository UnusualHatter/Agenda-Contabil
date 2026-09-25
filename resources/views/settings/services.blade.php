<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-page-header :title="__('services.title')" :subtitle="__('services.subtitle')" />

        <livewire:services.service-catalog />
    </div>
</x-app-layout>
