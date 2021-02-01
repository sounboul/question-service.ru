<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по вопросам
 */
class QuestionSearchForm
{
    /**
     * @var int|null Идентификатор вопроса
     */
    public ?int $id;

    /**
     * @var string|null Статус вопроса
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Question\Question", "getStatusList"}
     * )
     */
    public ?string $status;

    /**
     * @var string|null Поиск по тексту
     */
    public ?string $text;

    /**
     * @var int|null Категория
     */
    public ?int $categoryId;

    /**
     * @var int Автор
     */
    public ?int $userId;

    /**
     * @var string|null IP автора
     */
    public ?string $createdByIp;

    /**
     * @var bool|null Вопросы без ответа
     */
    public ?bool $withoutAnswers = null;

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
            'q.id_DESC' => 'ID, DESC',
            'q.id_ASC' => 'ID, ASC',

            'q.totalAnswers_DESC' => 'Количество ответов, DESC',
            'q.totalAnswers_ASC' => 'Количество ответов, ASC',
        ];
    }
}
