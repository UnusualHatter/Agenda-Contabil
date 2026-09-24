<x-app-layout>
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <x-flash-status />

        <livewire:appointments.appointment-details :appointment="$appointment" />
    </div>
</x-app-layout>
