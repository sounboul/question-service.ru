<?php
namespace App\Service\Question;

use App\Entity\Question\Question;
use App\Exception\ServiceException;
use App\Repository\Question\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис для работы с вопросами и ответами
 */
class QuestionService
{
    /**
     * @var QuestionRepository Question Repository
     */
    private QuestionRepository $questionRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * Конструктор сервиса
     *
     * @param QuestionRepository $questionRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->questionRepository = $questionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id Идентификатор
     * @return Question Получить вопрос по его идентификатору
     * @throws ServiceException В случае если вопрос не найден
     */
    public function getQuestionById(int $id): Question
    {
        $question = $this->questionRepository->findOneById($id);
        if (empty($question)) {
            throw new ServiceException("Не найден вопрос с указанным идентификатором");
        }

        return $question;
    }

    /**
     * @param int $categoryId Идентификатор категории
     * @return int Количество вопросов в указанной категории
     */
    public function countQuestionsByCategoryId(int $categoryId): int
    {
        return $this->questionRepository->countQuestionsByCategoryId($categoryId);
    }

    /**
     * Обновить количесто ответов у вопроса
     *
     * @param int $id Идентификатор вопроса
     * @param int $count Количество ответов
     * @return Question Вопрос
     * @throws ServiceException
     */
    public function updateTotalAnswers(int $id, int $count): Question
    {
        $question = $this->getQuestionById($id);
        $question->setTotalAnswers($count);

        return $this->updateQuestion($question);
    }

    /**
     * Процесс сохранения вопроса
     *
     * @param Question $question Question
     * @return Question Сохраненный вопрос
     */
    public function updateQuestion(Question $question): Question
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $question;
    }
}
