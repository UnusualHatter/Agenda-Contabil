<?php

declare(strict_types=1);

namespace App\Domain\Users\Enums;

/**
 * One role per user, checked through policies rather than by hiding buttons.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Member => 'Membro da equipe',
            self::Viewer => 'Visualizador',
        };
    }

    public function canWrite(): bool
    {
        return $this !== self::Viewer;
    }
}
