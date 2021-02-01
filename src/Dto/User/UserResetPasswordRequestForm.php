<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для запроса восстановления пароля
 */
class UserResetPasswordRequestForm
{
    /**
     * @var string E-mail
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     * @Assert\Email()
     */
    public string $email;
}
