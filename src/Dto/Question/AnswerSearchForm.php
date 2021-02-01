<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по ответам
 */
class AnswerSearchForm
{
    /**
     * @var int|null Идентификатор ответа
     */
    public ?int $id = null;

    /**
     * @var string|null Статус ответа
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Question\Answer", "getStatusList"}
     * )
     */
    public ?string $status = null;

    /**
     * @var string|null Поиск по тексту
     */
    public ?string $text = null;

    /**
     * @var int|null Вопрос
     */
    public ?int $questionId = null;

    /**
     * @var int|null Автор
     */
    public ?int $userId = null;

    /**
     * @var string|null IP автора
     */
    public ?string $createdByIp = null;

    /**
     * @var string|null Сортировка
     */
    public ?string $orderBy = null;

    /**
     * @return array Доступные варианты сортировки
     */
    public static function getAvailableOrderBy(): array
    {
        return [
            'a.id_DESC' => 'ID, DESC',
            'a.id_ASC' => 'ID, ASC',
        ];
    }
}
