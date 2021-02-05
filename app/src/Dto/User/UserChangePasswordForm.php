<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для изменения пароля пользователю
 */
class UserChangePasswordForm
{
    /**
     * @var int Идентификатор пользователя
     *
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     */
    public int $id;

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
