<?php
namespace App\Dto\QuestionSearch;

/**
 * Простая форма для листинга вопросов
 * с возможностью полнотекстового поиска
 * и фильтрации по категориям.
 */
class SimpleSearchForm
{
    /**
     * @var int|null Категория
     */
    public ?int $categoryId = null;

    /**
     * @var string|null Поисковой запрос
     */
    public ?string $query = null;

    /**
     * @var int Номер страницы
     */
    public int $page = 1;

    /**
     * @var int Размер страница
     */
    public int $pageSize = 20;
}
