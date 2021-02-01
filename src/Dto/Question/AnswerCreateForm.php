<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для формы создания ответа
 */
class AnswerCreateForm
{
    /**
     * @var int|null User
     */
    public ?int $userId;

    /**
     * @var int Вопрос
     */
    public int $questionId;

    /**
     * @var string Текст ответа
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=10,
     *     max=2000
     * )
     */
    public string $text;

    /**
     * @var string|null IP
     */
    public ?string $createdByIp;
}
