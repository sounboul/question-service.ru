<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для создания категории
 */
class CategoryCreateForm
{
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

    /**
     * @var string|null Описание категории
     *
     * @Assert\Type("string")
     * @Assert\Length(
     *     max=3000
     * )
     */
    public ?string $description;
}
