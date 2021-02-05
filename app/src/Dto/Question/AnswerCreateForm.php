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
     *
     * @Assert\Type("integer")
     */
    public ?int $userId;

    /**
     * @var int Вопрос
     *
     * @Assert\Type("integer")
     */
    public int $questionId;

    /**
     * @var string Текст ответа
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=10,
     *     max=2000
     * )
     */
    public string $text;

    /**
     * @var string|null IP
     *
     * @Assert\Type("string")
     * @Assert\Length(
     *     max=46
     * )
     */
    public ?string $createdByIp;
}
