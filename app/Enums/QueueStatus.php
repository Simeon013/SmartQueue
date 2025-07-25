<?php

namespace App\Enums;

enum QueueStatus: string
{
    case OPEN = 'open';
    case PAUSED = 'paused';
    case BLOCKED = 'blocked';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Ouverte/Active',
            self::PAUSED => 'En pause',
            self::BLOCKED => 'Bloquée',
            self::CLOSED => 'Fermée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'success',
            self::PAUSED => 'warning',
            self::BLOCKED => 'danger',
            self::CLOSED => 'secondary',
        };
    }

    public function canCreateTickets(): bool
    {
        return match($this) {
            self::OPEN, self::PAUSED => true,
            default => false,
        };
    }

    public function canProcessTickets(): bool
    {
        return match($this) {
            self::OPEN, self::BLOCKED => true,
            default => false,
        };
    }

    public function canBeModified(): bool
    {
        return $this !== self::CLOSED;
    }

    public static function toSelectArray(): array
    {
        return collect(self::cases())->mapWithKeys(fn($status) => [
            $status->value => $status->label()
        ])->toArray();
    }
}
