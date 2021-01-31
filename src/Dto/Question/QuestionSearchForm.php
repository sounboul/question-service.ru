<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по вопросам
 */
class QuestionSearchForm
{
    /**
     * @var int Идентификатор вопроса
     */
    public int $id;

    /**
     * @var string Статус вопроса
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Question\Question", "getStatusList"}
     * )
     */
    public string $status;

    /**
     * @var string Поиск по тексту
     */
    public string $text;

    /**
     * @var int Категория
     */
    public int $categoryId;

    /**
     * @var int Автор
     */
    public int $userId;

    /**
     * @var string IP автора
     */
    public string $createdByIp;

    /**
     * @var bool Вопросы без ответа
     */
    public ?bool $withoutAnswers = null;

    /**
     * @var string Сортировка
     */
    public string $orderBy;

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
