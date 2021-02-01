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
    public ?int $id;

    /**
     * @var string|null Статус ответа
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Question\Answer", "getStatusList"}
     * )
     */
    public ?string $status;

    /**
     * @var string|null Поиск по тексту
     */
    public ?string $text;

    /**
     * @var int|null Вопрос
     */
    public ?int $questionId;

    /**
     * @var int|null Автор
     */
    public ?int $userId;

    /**
     * @var string|null IP автора
     */
    public ?string $createdByIp;

    /**
     * @var string|null Сортировка
     */
    public ?string $orderBy;

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
