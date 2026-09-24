<?php

declare(strict_types=1);

namespace App\Domain\Clients\Queries;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;

final class SearchClients
{
    /**
     * Phone and document are compared by digits only, so
     * "51 99999" finds "(51) 99999-0000" and "123.456" finds "12345678900".
     *
     * @return Builder<Client>
     */
    public static function query(string $term): Builder
    {
        $term = trim($term);
        $like = '%'.addcslashes($term, '%_\\').'%';
        $digits = preg_replace('/\D/', '', $term);

        return Client::query()
            ->when($term !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($like, $digits): void {
                $query->whereLike('name', $like)
                    ->orWhereLike('trade_name', $like)
                    ->orWhereLike('email', $like)
                    ->when($digits !== '', fn (Builder $query) => $query
                        ->orWhereRaw("regexp_replace(coalesce(phone, ''), '\\D', '', 'g') like ?", ["%{$digits}%"])
                        ->orWhereRaw("regexp_replace(coalesce(document, ''), '\\D', '', 'g') like ?", ["%{$digits}%"]));
            }))
            ->orderByDesc('active')
            ->orderBy('name');
    }
}
