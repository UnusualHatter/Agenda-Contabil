<?php

declare(strict_types=1);

namespace App\Domain\Clients\Queries;

use App\Models\Client;
use App\Support\BlindIndex;
use Illuminate\Database\Eloquent\Builder;

final class SearchClients
{
    /**
     * The document is encrypted, so it only matches as a whole, through its index.
     *
     * @return Builder<Client>
     */
    public static function query(string $term): Builder
    {
        $term = mb_substr(trim($term), 0, 100);
        $like = '%'.addcslashes($term, '%_\\').'%';
        $digits = preg_replace('/\D/', '', $term);

        return Client::query()
            ->when($term !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($like, $digits): void {
                $query->whereLike('name', $like)
                    ->orWhereLike('trade_name', $like)
                    ->orWhereLike('email', $like)
                    ->when($digits !== '', fn (Builder $query) => $query
                        ->orWhereRaw("regexp_replace(coalesce(phone, ''), '\\D', '', 'g') like ?", ["%{$digits}%"])
                        ->orWhere('document_index', BlindIndex::forDocument($digits)));
            }))
            ->orderByDesc('active')
            ->orderBy('name');
    }
}
