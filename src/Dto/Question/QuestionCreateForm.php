<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для формы создания вопроса
 */
class QuestionCreateForm
{
    /**
     * @var int|null User
     */
    public ?int $userId;

    /**
     * @var int Category
     */
    public int $categoryId;

    /**
     * @var string Название вопроса
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=3,
     *     max=200
     * )
     */
    public string $title;

    /**
     * @var string|null Текст вопроса
     *
     * @Assert\Length(
     *     max=2000
     * )
     */
    public ?string $text;

    /**
     * @var string|null IP
     */
    public ?string $createdByIp;
}
