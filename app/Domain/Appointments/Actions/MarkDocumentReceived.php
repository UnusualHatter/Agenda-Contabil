<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Actions;

use App\Models\AppointmentDocument;

final class MarkDocumentReceived
{
    public function handle(AppointmentDocument $document, bool $received): AppointmentDocument
    {
        if ($document->isReceived() === $received) {
            return $document;
        }

        $document->update(['received_at' => $received ? now() : null]);

        return $document;
    }
}
