<?php
namespace App\Utils\User;

/**
 * Генератор случайного пароля заданной длинны
 */
class PasswordGenerator
{
    /**
     * @param int $length Длинна
     * @return string Случайный пароль заданной длинны
     *
     * @throws \Exception
     */
    public static function generate(int $length = 10) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
        $password = '';
        $characterListLength = mb_strlen($characters, '8bit') - 1;

        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }

        return $password;
    }
}
