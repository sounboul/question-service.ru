<?php
namespace App\EventListener\Question;

use App\Entity\Question\Answer;
use App\Service\Question\AnswerService;
use App\Exception\ServiceException;
use App\Service\Question\QuestionService;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Модель событий для сущности "Ответ"
 */
class AnswerListener
{
    /**
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * @var AnswerService Answer Service
     */
    private AnswerService $answerService;

    /**
     * Конструктор
     *
     * @param QuestionService $questionService Question Service
     * @param AnswerService $answerService Answer Service
     */
    public function __construct(
        QuestionService $questionService,
        AnswerService $answerService
    )
    {
        $this->questionService = $questionService;
        $this->answerService = $answerService;
    }

    /**
     * Событие, которое вызвано после создания ответа
     *
     * @param Answer $answer
     * @param LifecycleEventArgs $eventArgs
     * @throws ServiceException
     */
    public function postPersist(Answer $answer, LifecycleEventArgs $eventArgs)
    {
        // пересчитать количество ответов у вопроса
        $this->recountAnswersInQuestion($answer->getQuestion()->getId());
    }

    /**
     * Событие, которое вызвано после обновления ответа
     *
     * @param Answer $answer
     * @param LifecycleEventArgs $eventArgs
     * @throws ServiceException
     */
    public function postUpdate(Answer $answer, LifecycleEventArgs $eventArgs)
    {
        // пересчитать количество ответов у вопроса
        $this->recountAnswersInQuestion($answer->getQuestion()->getId());
    }

    /**
     * Обновление количества ответов у вопроса
     *
     * @param int $questionId Идентификатор вопроса
     * @return void
     * @throws ServiceException
     */
    private function recountAnswersInQuestion(int $questionId)
    {
        $count = $this->answerService->countAnswersByQuestionId($questionId);
        $this->questionService->updateTotalAnswers($questionId, $count);
    }
}
