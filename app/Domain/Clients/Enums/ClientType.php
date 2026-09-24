<?php

declare(strict_types=1);

namespace App\Domain\Clients\Enums;

enum ClientType: string
{
    case Individual = 'individual';
    case Organization = 'organization';

    public function label(): string
    {
        return __("clients.type.{$this->value}");
    }
}
