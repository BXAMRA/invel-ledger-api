<?php

namespace App\Support;

class PasswordGenerator
{
    /**
     * Generate a secure, unambiguous password.
     * 
     * Uses a mix of lowercase alphabets and numbers, excluding
     * confusing characters like 0, o, i, l, 1.
     * 
     * @param int $length
     * @return string
     */
    public static function generate(int $length = 8): string
    {
        $pool = 'abcdefghjkmnpqrstuvwxyz23456789@!$#?';
        
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
