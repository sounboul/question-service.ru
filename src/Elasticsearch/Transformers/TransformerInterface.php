<?php
namespace App\Elasticsearch\Transformers;

use App\Elasticsearch\Model\Base;

/**
 * Интерфейс для Transformer классов
 */
interface TransformerInterface
{
    /**
     * @return Base Сущность в представлении Elasticsearch
     */
    public function getModel(): Base;
}
