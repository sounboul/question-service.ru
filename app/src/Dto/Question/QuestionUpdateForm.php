<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для формы редактирования вопроса
 */
class QuestionUpdateForm
{
    /**
     * @var int Идентификатор вопроса
     *
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     */
    public int $id;

    /**
     * @var int Категория
     *
     * @Assert\Type("integer")
     */
    public int $categoryId;

    /**
     * @var string Название вопроса
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
}
