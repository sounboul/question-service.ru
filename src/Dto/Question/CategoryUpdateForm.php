<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для редактирования категории
 */
class CategoryUpdateForm
{
    /**
     * @var int Идентификатор категории
     *
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     */
    public int $id;

    /**
     * @var string Название категории
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     */
    public string $title;

    /**
     * @var string|null Slug категории
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
