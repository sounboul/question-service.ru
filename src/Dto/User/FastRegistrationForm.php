<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для быстрой регистрации пользователя (на основе E-mail адреса)
 */
class FastRegistrationForm
{
    /**
     * @var string E-mail
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     * @Assert\Email()
     */
    public string $email;
}
