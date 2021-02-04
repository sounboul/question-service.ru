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
     *
     * @Assert\Type("integer")
     */
    public ?int $userId;

    /**
     * @var int Категория
     *
     * @Assert\Type("integer")
     */
    public int $categoryId;

    /**
     * @var string Текст вопроса
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=3,
     *     max=200
     * )
     */
    public string $title;

    /**
     * @var string|null Текст вопроса
     *
     * @Assert\Type("string")
     * @Assert\Length(
     *     max=2000
     * )
     */
    public ?string $text = null;

    /**
     * @var string|null Slug вопроса
     *
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     * @Assert\Regex(
     *     pattern="/^[-_\w]+$/"
     * )
     */
    public ?string $slug;

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
