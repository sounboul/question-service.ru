<?php
namespace App\Service\Question;

use App\Entity\Question\Answer;
use App\Exception\ServiceException;
use App\Repository\Question\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис для работы с ответами
 */
class AnswerService
{
    /**
     * @var AnswerRepository Answer Repository
     */
    private AnswerRepository $answerRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * Конструктор сервиса
     *
     * @param AnswerRepository $answerRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        AnswerRepository $answerRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->answerRepository = $answerRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id Идентификатор ответа
     * @return Answer Получить ответ по его идентификатору
     * @throws ServiceException В случае если ответ не найден
     */
    public function getById(int $id): Answer
    {
        $answer = $this->answerRepository->findOneById($id);
        if (empty($answer)) {
            throw new ServiceException("Не найден ответ с ID '{$id}'");
        }

        return $answer;
    }

    /**
     * @param int $questionId Идентификатор вопроса
     * @return int Количество ответов к указанному вопросу
     */
    public function countAnswersByQuestionId(int $questionId): int
    {
        return $this->answerRepository->countAnswersByQuestionId($questionId);
    }

    /**
     * Процесс сохранения ответа
     *
     * @param Answer $answer Answer
     * @return Answer Сохраненный ответ
     */
    private function save(Answer $answer): Answer
    {
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        return $answer;
    }
}
