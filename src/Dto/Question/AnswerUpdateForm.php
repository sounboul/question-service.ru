<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для формы редактирования ответа
 */
class AnswerUpdateForm
{
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
}
