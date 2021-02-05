<?php
namespace App\Elasticsearch\Model;

/**
 * Модель одного вопроса в представлении ElasticSearch (согласно маппингу)
 */
class Question extends Base
{
    /**
     * @inheritdoc
     */
    public static string $indexName = 'question';

    /**
     * @var int Идентификатор
     */
    public int $id;

    /**
     * @var array|null Пользователь
     */
    public ?array $user;

    /**
     * @var array|null Категория
     */
    public ?array $category;

    /**
     * @var string Заголовок
     */
    public string $title;

    /**
     * @var string Текст
     */
    public string $text;

    /**
     * @var string Относительная ссылка на вопрос
     */
    public string $href;

    /**
     * @var int Количество ответов
     */
    public int $totalAnswers;

    /**
     * @var \DateTime|null Дата и время создания
     */
    public ?\DateTime $createdAt;
}
