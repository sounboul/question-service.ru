<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для создания/редактирования категории
 */
class CategoryForm
{
    /**
     * @var string Название категории
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=100)
     */
    public string $title;

    /**
     * @var string Slug категории
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=100)
     * @Assert\Regex(pattern="/^[-_\w]+$/")
     */
    public string $slug;
}
