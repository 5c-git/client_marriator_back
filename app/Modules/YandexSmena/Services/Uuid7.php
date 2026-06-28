<?php

namespace Modules\YandexSmena\Services;

class Uuid7
{
    /**
     * Generate a UUID v7 string (time-ordered, sortable).
     *
     * Format:
     *   48-bit Unix timestamp in milliseconds
     *   4-bit version = 7
     *   12-bit rand_a
     *   2-bit variant = 10
     *   62-bit rand_b
     */
    public static function generate(): string
    {
        $timeMs = (int) (microtime(true) * 1000);
        $timeHex = str_pad(dechex($timeMs), 12, '0', STR_PAD_LEFT);

        $random = bin2hex(random_bytes(10)); // 80 bits; we need 74 + 6 variant bits

        return sprintf(
            '%08s-%04s-7%03s-%s%03s-%012s',
            substr($timeHex, 0, 8),
            substr($timeHex, 8, 4),
            substr($random, 0, 3),
            '8', // variant 10xx -> 8
            substr($random, 3, 3),
            substr($random, 6, 12)
        );
    }
}
