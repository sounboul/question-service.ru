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
    public ?int $id = null;

    /**
     * @var string|null Статус вопроса
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Question\Question", "getStatusList"}
     * )
     */
    public ?string $status = null;

    /**
     * @var string|null Поиск по тексту
     */
    public ?string $text = null;

    /**
     * @var int|null Категория
     */
    public ?int $categoryId = null;

    /**
     * @var int|null Автор
     */
    public ?int $userId = null;

    /**
     * @var string|null IP автора
     */
    public ?string $createdByIp = null;

    /**
     * @var bool|null Вопросы без ответа
     */
    public ?bool $withoutAnswers = null;

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
            'q.id_DESC' => 'ID, DESC',
            'q.id_ASC' => 'ID, ASC',

            'q.totalAnswers_DESC' => 'Количество ответов, DESC',
            'q.totalAnswers_ASC' => 'Количество ответов, ASC',
        ];
    }
}
