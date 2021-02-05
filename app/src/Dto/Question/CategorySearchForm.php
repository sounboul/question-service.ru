<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по категориями
 */
class CategorySearchForm
{
    /**
     * @var int|null Идентификатор категории
     */
    public ?int $id = null;

    /**
     * @var string|null Статус категории
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Question\Category", "getStatusList"}
     * )
     */
    public ?string $status = null;

    /**
     * @var string|null Название категории
     */
    public ?string $title = null;

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
            'c.id_DESC' => 'ID, DESC',
            'c.id_ASC' => 'ID, ASC',

            'c.totalQuestions_DESC' => 'Количество вопросов, DESC',
            'c.totalQuestions_ASC' => 'Количество вопросов, ASC',
        ];
    }
}
