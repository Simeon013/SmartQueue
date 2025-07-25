<?php

namespace App\Traits;

trait FormatsDuration
{
    /**
     * Formate une durée en secondes en une chaîne lisible
     * Exemple: 3665 -> "1h 1m 5s"
     */
    public function formatDuration($seconds)
    {
        if ($seconds === null || $seconds === 0) {
            return '0s';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        if (($remainingSeconds > 0 && count($parts) < 2) || empty($parts)) {
            $parts[] = $remainingSeconds . 's';
        }

        return implode(' ', $parts);
    }

    /**
     * Formate une durée en secondes en une version courte
     * Exemple: 3665 -> "1h 1min"
     */
    public function formatShortDuration($seconds)
    {
        if ($seconds === null || $seconds === 0) {
            return '0s';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '');
        }
        
        return $minutes > 0 ? $minutes . 'min' : floor($seconds % 60) . 's';
    }
}
