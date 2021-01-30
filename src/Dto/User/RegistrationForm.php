<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для регистрации пользователя
 */
class RegistrationForm
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

    /**
     * @var string Пароль
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=8,
     *     max=100
     * )
     */
    public string $password;

    /**
     * @var bool С правилами ознакомлен
     *
     * @Assert\IsTrue(
     *     message="Вы должны согласиться с правилами нашего сервиса"
     * )
     */
    public bool $agreeTerms;
}
