<?php
namespace App\Dto\Question;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по категориями
 */
class CategorySearchForm
{
    /**
     * @var int Идентификатор категории
     */
    public int $id;

    /**
     * @var string Статус категории
     */
    public string $status;

    /**
     * @var string Название категории
     */
    public string $title;

    /**
     * @var array Сортировка результатов поиска
     */
    private array $orderBy = [];

    /**
     * @return array Сортировка результатов поиска
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }
}
