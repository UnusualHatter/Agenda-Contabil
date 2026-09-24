<span class="document-check" x-bind:class="received && 'document-check--on'" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <path class="document-check__mark" d="M5 12.5l4.5 4.5L19 7.5" />
    </svg>
</span>
<span class="flex-1 text-ink">{{ $name }}</span>
<span class="shrink-0 text-xs font-semibold transition-colors"
      x-bind:class="received ? 'text-moss' : 'text-ink-muted'"
      x-text="received ? @js(__('appointments.row.received_label')) : @js(__('appointments.row.pending_label'))"></span>
