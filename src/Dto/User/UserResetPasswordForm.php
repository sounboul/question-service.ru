<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для сброса пароля пользователю через форму восстановления пароля
 */
class UserResetPasswordForm
{
    /**
     * @var string Токен сброса пароля
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     max=100
     * )
     */
    public string $token;

    /**
     * @var string Пароль
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=8,
     *     max=100
     * )
     */
    public string $password;
}
