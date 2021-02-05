<?php
namespace App\Utils\User;

/**
 * Генератор токена заданной длинны
 */
class TokenGenerator
{
    /**
     * @param int $length Длинна
     * @return string Случайный токен заданной длинны
     *
     * @throws \Exception
     */
    public static function generate(int $length) : string
    {
        return bin2hex(random_bytes($length));
    }
}
