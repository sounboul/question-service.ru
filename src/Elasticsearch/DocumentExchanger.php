<?php
namespace App\Elasticsearch;

use App\Elasticsearch\Model\Question;
use App\Elasticsearch\Transformers\QuestionTransformer;
use App\Service\Question\QuestionService;
use Elastica\Document;
use App\Exception\ServiceException;
use JoliCode\Elastically\Messenger\DocumentExchangerInterface;

/**
 * Данный компонент обеспечивает индексацию документов в релаьном режиме времени
 */
class DocumentExchanger implements DocumentExchangerInterface
{
    /**
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * Конструктор
     *
     * @param QuestionService $questionService Question Service
     */
    public function __construct(
        QuestionService $questionService
    )
    {
        $this->questionService = $questionService;
    }

    /**
     * Индексация документа
     *
     * @param string $className Название класса
     * @param string $id Идентификатор
     * @return Document|null
     * @throws ServiceException
     */
    public function fetchDocument(string $className, string $id): ?Document
    {
        if ($className === Question::class) {
            $question = $this->questionService->getById($id);
            if ($question->isActive()) {
                return new Document($id, (new QuestionTransformer($question))->getModel());
            }
        }

        return null;
    }
}
