<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для формы редактирования ответа
 */
class AnswerUpdateForm
{
    /**
     * @var int Идентификатор ответа
     *
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     */
    public int $id;

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
}
