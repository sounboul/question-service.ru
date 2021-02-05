<?php
namespace App\Service\Question;

use App\Entity\Question\Question;
use App\Exception\ServiceException;
use App\Exception\AppException;
use App\Exception\EntityValidationException;
use App\Repository\Question\QuestionRepository;
use App\Utils\SlugHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\Question\QuestionSearchForm;
use App\Dto\Question\QuestionCreateForm;
use App\Pagination\Paginator;
use App\Service\User\UserService;
use App\Dto\Question\QuestionUpdateForm;
use Doctrine\ORM\Query;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Сервис для работы с вопросами и ответами
 */
class QuestionService
{
    /**
     * @var UserService User Service
     */
    private UserService $userService;

    /**
     * @var CategoryService Category Service
     */
    private CategoryService $categoryService;

    /**
     * @var QuestionRepository Question Repository
     */
    private QuestionRepository $questionRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var ValidatorInterface Validator Interface
     */
    private ValidatorInterface $validator;

    /**
     * Конструктор сервиса
     *
     * @param UserService $userService
     * @param CategoryService $categoryService
     * @param QuestionRepository $questionRepository
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        UserService $userService,
        CategoryService $categoryService,
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    )
    {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
        $this->questionRepository = $questionRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param int $id Идентификатор вопроса
     * @return Question Получить вопрос по его идентификатору
     * @throws ServiceException В случае если вопрос не найден
     */
    public function getById(int $id): Question
    {
        $question = $this->questionRepository->findOneById($id);
        if (empty($question)) {
            throw new ServiceException("Не найден вопрос с ID '{$id}'");
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
     * Создание вопроса
     *
     * @param QuestionCreateForm $form
     * @return Question Созданный вопрос
     * @throws AppException
     */
    public function create(QuestionCreateForm $form): Question
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы QuestionCreateForm");
        }

        if (empty($form->slug)) {
            $form->slug = SlugHelper::generate($form->title);
        }

        $question = new Question();
        $question->setStatus(Question::STATUS_ACTIVE);

        if (!empty($form->userId)) {
            $question->setUser($this->userService->getUserById($form->userId));
        }

        $question->setCategory($this->categoryService->getById($form->categoryId));
        $question->setTitle($form->title);
        $question->setText((string) $form->text);
        $question->setSlug($form->slug);
        $question->setCreatedByIp((string) $form->createdByIp);

        return $this->save($question);
    }

    /**
     * Обновление вопроса
     *
     * @param QuestionUpdateForm $form
     * @return Question Обновленный вопрос
     * @throws AppException
     */
    public function update(QuestionUpdateForm $form): Question
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы QuestionUpdateForm");
        }

        if (empty($form->slug)) {
            $form->slug = SlugHelper::generate($form->title);
        }

        $question = $this->getById($form->id);
        $question->setCategory($this->categoryService->getById($form->categoryId));
        $question->setTitle($form->title);
        $question->setText((string) $form->text);
        $question->setSlug($form->slug);

        return $this->save($question);
    }

    /**
     * Листинг вопросов с фильтрацией
     *
     * @param QuestionSearchForm $form Форма поиска
     * @param int $page Номер страницы
     * @param int $pageSize Количество записей на страницу
     * @return Paginator Результат выборка с постраничным выводом
     * @throws ServiceException
     */
    public function listing(QuestionSearchForm $form, $page = 1, $pageSize = 30): Paginator
    {
        try {
            $query = $this->questionRepository->listingFilter($form);
            return (new Paginator($query, $pageSize))->paginate($page);
        } catch (\Exception $e) {
            throw new ServiceException($e->getMessage());
        }
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
        $question = $this->getById($id);
        $question->setTotalAnswers($count);

        return $this->save($question);
    }

    /**
     * Удаление вопроса
     *
     * @param int $id Идентификатор вопроса
     * @return Question Удаленный вопрос
     * @throws ServiceException|EntityValidationException
     */
    public function delete(int $id): Question
    {
        $question = $this->getById($id);
        if ($question->isDeleted()) {
            throw new ServiceException("Вопрос уже удален");
        }

        $question->setStatus(Question::STATUS_DELETED);
        return $this->save($question);
    }

    /**
     * Восстановление вопроса
     *
     * @param int $id Идентификатор вопроса
     * @return Question Восстановленный вопрос
     * @throws ServiceException|EntityValidationException
     */
    public function restore(int $id): Question
    {
        $question = $this->getById($id);
        if ($question->isActive()) {
            throw new ServiceException("Вопрос уже активен");
        }

        $question->setStatus(Question::STATUS_ACTIVE);
        return $this->save($question);
    }

    /**
     * Процесс сохранения вопроса
     *
     * @param Question $question Question
     * @return Question Сохраненный вопрос
     */
    private function save(Question $question): Question
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $question;
    }
}
