<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для регистрации пользователя
 */
class UserRegistrationForm
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

    /**
     * @var bool С правилами ознакомлен
     *
     * @Assert\IsTrue(
     *     message="Вы должны согласиться с правилами нашего сервиса"
     * )
     */
    public bool $agreeTerms;
}
