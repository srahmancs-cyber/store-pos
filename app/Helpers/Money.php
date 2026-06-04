<?php

namespace App\Helpers;

class Money
{
    /**
     * Convert a decimal currency string/float to integer cents.
     * e.g. "19.99" → 1999
     */
    public static function toCents(mixed $value): int
    {
        return (int) round((float) $value * 100);
    }

    /**
     * Convert integer cents to decimal.
     * e.g. 1999 → 19.99
     */
    public static function fromCents(int $cents): float
    {
        return $cents / 100;
    }

    /**
     * Format cents as currency string with symbol.
     * e.g. 1999, '$' → "$19.99"
     */
    public static function format(int $cents, string $symbol = ''): string
    {
        return $symbol . number_format($cents / 100, 2);
    }
}
