<?php
namespace App\Elasticsearch\Model;

/**
 * Базовая модель сущности в представлении ElasticSearch (согласно маппингу)
 */
abstract class Base
{
    /**
     * @var string Название поискового индекса
     */
    public static string $indexName;
}
