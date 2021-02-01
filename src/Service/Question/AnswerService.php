<?php
namespace App\Service\Question;

use App\Dto\Question\AnswerSearchForm;
use App\Dto\Question\AnswerCreateForm;
use App\Dto\Question\AnswerUpdateForm;
use App\Entity\Question\Answer;
use App\Exception\EntityValidationException;
use App\Exception\ServiceException;
use App\Exception\AppException;
use App\Pagination\Paginator;
use App\Repository\Question\AnswerRepository;
use App\Service\User\UserService;
use App\Service\Question\QuestionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Сервис для работы с ответами
 */
class AnswerService
{
    /**
     * @var UserService User Service
     */
    private UserService $userService;

    /**
     * @var QuestionService Question Service
     */
    private QuestionService $questionService;

    /**
     * @var AnswerRepository Answer Repository
     */
    private AnswerRepository $answerRepository;

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
     * @param QuestionService $questionService
     * @param AnswerRepository $answerRepository
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        UserService $userService,
        QuestionService $questionService,
        AnswerRepository $answerRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    )
    {
        $this->userService = $userService;
        $this->questionService = $questionService;
        $this->answerRepository = $answerRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
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
            throw new ServiceException("Не найден ответ с ID '$id'");
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
     * Создание ответа
     *
     * @param AnswerCreateForm $form
     * @return Answer Созданный ответ
     * @throws AppException
     */
    public function create(AnswerCreateForm $form): Answer
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы");
        }

        $answer = new Answer();
        $answer->setStatus(Answer::STATUS_ACTIVE);
        $answer->setQuestion($this->questionService->getById($form->questionId));

        if (!empty($form->userId)) {
            $answer->setUser($this->userService->getUserById($form->userId));
        }

        $answer->setText($form->text);
        $answer->setCreatedByIp((string) $form->createdByIp);

        return $this->save($answer);
    }

    /**
     * Обновление ответа
     *
     * @param int $id Идентификатор ответа
     * @param AnswerUpdateForm $form
     * @return Answer Обновлённый ответ
     * @throws AppException
     */
    public function update(int $id, AnswerUpdateForm $form): Answer
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы");
        }

        $question = $this->getById($id);
        $question->setText($form->text);

        return $this->save($question);
    }

    /**
     * Листинг ответов с фильтрацией
     *
     * @param AnswerSearchForm $form Форма поиска
     * @param int $page Номер страницы
     * @param int $pageSize Количество записей на страницу
     * @return Paginator Результат выборка с постраничным выводом
     * @throws ServiceException
     */
    public function listing(AnswerSearchForm $form, $page = 1, $pageSize = 30): Paginator
    {
        try {
            $query = $this->answerRepository->listingFilter($form);
            return (new Paginator($query, $pageSize))->paginate($page);
        } catch (\Exception $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * Удаление ответа
     *
     * @param int $id Идентификатор ответа
     * @return Answer Удаленный ответ
     * @throws ServiceException|EntityValidationException
     */
    public function delete(int $id): Answer
    {
        $answer = $this->getById($id);
        if ($answer->isDeleted()) {
            throw new ServiceException("Ответ уже удален");
        }

        $answer->setStatus(Answer::STATUS_DELETED);
        return $this->save($answer);
    }

    /**
     * Восстановление ответа
     *
     * @param int $id Идентификатор ответа
     * @return Answer Восстановленный ответ
     * @throws ServiceException|EntityValidationException
     */
    public function restore(int $id): Answer
    {
        $answer = $this->getById($id);
        if ($answer->isActive()) {
            throw new ServiceException("Ответ уже активен");
        }

        $answer->setStatus(Answer::STATUS_ACTIVE);
        return $this->save($answer);
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
