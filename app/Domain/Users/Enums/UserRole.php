<?php

declare(strict_types=1);

namespace App\Domain\Users\Enums;

/**
 * Application roles, as described in the PRD (section 3).
 *
 * Roles are intentionally simple for the MVP: a single role per user, checked
 * through policies and gates rather than by hiding buttons in the UI.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Member = 'member';
    case Viewer = 'viewer';

    /**
     * Human readable label, shown in the pt-BR interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Member => 'Membro da equipe',
            self::Viewer => 'Visualizador',
        };
    }

    /**
     * Whether this role may create or change data.
     */
    public function canWrite(): bool
    {
        return $this !== self::Viewer;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $role): array => $carry + [$role->value => $role->label()],
            [],
        );
    }
}
