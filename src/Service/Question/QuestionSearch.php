<?php
namespace App\Service\Question;

use App\Dto\QuestionElastic\SimpleSearchForm;
use App\Elasticsearch\Model\Question;
use App\Exception\ServiceException;
use Elastica\Query;
use Elastica\ResultSet;
use JoliCode\Elastically\Client;

/**
 * Сервис для работы с листингами, поиском и фильтрацией вопросов.
 * Реализация через поисковой движок ElasticSearch.
 */
class QuestionSearch
{
    /**
     * @var Client Client
     */
    private Client $client;

    /**
     * Конструктор
     *
     * @param Client $client Client
     */
    public function __construct(
        Client $client
    )
    {
        $this->client = $client;
    }

    /**
     * Простой поиск по вопросам.
     *
     * @param SimpleSearchForm $form Форма поиска
     * @return ResultSet Результат поиска
     * @throws ServiceException В случае ошибки
     */
    public function simple(SimpleSearchForm $form): ResultSet
    {
        // ES рекомендурет не выходить за лимит [from+size] в 10000.
        if ($form->page * $form->pageSize > 10000) {
            throw new ServiceException("Превышен лимит количества результатов поиска");
        }

        if ($form->page < 1 || $form->page > 300) {
            throw new ServiceException("'page' должен быть в пределах от 1 до 300");
        }

        // общая структура запроса
        $condition = [
            'query' => [
                'bool' => [],
            ],
        ];

        // применение фильтров (AND по filters условиям)
        $filters = [];
        if (!empty($form->categoryId)) {
            $filters[] = ['term' => ['category.id' => $form->categoryId]];
        }

        if (!empty($filters)) {
            $condition['query']['bool']['filter']['bool']['must'] = $filters;
        }

        // полнотекстовой поиск
        if (!empty($form->query)) {
            $condition['query']['bool']['minimum_should_match'] = 1;
            $condition['query']['bool']['should'] = [
                'multi_match' => [
                    'query' => $this->prepareMatchString($form->query),
                    'fields' => ['title^5', 'text'],
                    'operator' => 'and',
                    'minimum_should_match' => '70%',
                ]
            ];
        }

        $params = empty($condition['query']['bool']) ? null : $condition;
        $query = new Query($params);
        $query->setFrom($form->pageSize * ($form->page - 1));
        $query->setSize($form->pageSize);
        if (!empty($form->query)) {
            $query->setSort(['_score' => 'desc']);
        } else {
            $query->setSort(['createdAt' => 'desc']);
        }

        return $this->client->getIndex(Question::$indexName)->search($query);
    }

    /**
     * @param string $query Исходный поисковой запрос
     * @return string Подготовленый поисковой запрос
     * @throws ServiceException
     */
    private function prepareMatchString(string $query): string
    {
        $query = trim(strip_tags($query));

        // вырезает из запроса непечатные символы
        $query = preg_replace("/[\\x00-\\x09\\x0B-\\x1F]/", "", $query);

        if (mb_strlen($query) < 3 || mb_strlen($query) > 150) {
            throw new ServiceException("Поисковой запрос должен содержать от 3 до 150 символов");
        }

        return $query;
    }
}
