<?php
namespace App\Service\Question;

use App\Entity\Question\Answer;
use App\Entity\Question\Category;
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
     * @var CategoryService Category Service
     */
    private CategoryService $categoryService;

    /**
     * @var AnswerService Answer Service
     */
    private AnswerService $answerService;

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
     * @param CategoryService $categoryService
     * @param AnswerService $answerService
     * @param QuestionRepository $questionRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CategoryService $categoryService,
        AnswerService $answerService,
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->categoryService = $categoryService;
        $this->answerService = $answerService;
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
     * Процесс сохранения категории
     *
     * @param Category $category Category
     * @return Category Сохраненная категория
     */
    public function updateCategory(Category $category): Category
    {
        // обновление количества вопросов в категории
        if (!empty($category->getCreatedAt())) {
            $category->setTotalQuestions(
                $this->questionRepository->countQuestionsByCategoryId($category->getId())
            );
        }

        // сохранение категории
        return $this->categoryService->updateCategory($category);
    }

    /**
     * Процесс сохранения вопроса
     *
     * @param Question $question Question
     * @return Question Сохраненный вопрос
     */
    public function updateQuestion(Question $question): Question
    {
        // обновлние totalAnswers у вопроса
        if (!empty($question->getCreatedAt())) {
            $question->setTotalAnswers($this->answerService->countAnswersByQuestionId($question->getId()));
        }

        // обновление timestamp
        $question->updatedTimestamps();

        // сохранение в БД
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        // формирование href
        $question->setHref("/answer/".$question->getId()."_".$question->getSlug()."/");

        // повторное сохранение в БД
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        // обновление категории
        $this->updateCategory($question->getCategory());

        return $question;
    }

    /**
     * Процесс сохранения ответа
     *
     * @param Answer $answer Answer
     * @return Answer Сохраненный ответ
     */
    public function updateAnswer(Answer $answer): Answer
    {
        // обновление ответа
        $answer = $this->answerService->updateAnswer($answer);

        // обновление вопроса
        $this->updateQuestion($answer->getQuestion());

        return $answer;
    }
}
