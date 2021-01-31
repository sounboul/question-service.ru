<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для формы редактирования вопроса
 */
class QuestionUpdateForm
{
    /**
     * @var int|null Category
     */
    public ?int $categoryId;

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
     * @var string Slug вопроса
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     * @Assert\Regex(
     *     pattern="/^[-_\w]+$/"
     * )
     */
    public string $slug;
}
