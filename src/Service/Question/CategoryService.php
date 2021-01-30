<?php
namespace App\Service\Question;

use App\Entity\Question\Category;
use App\Exception\ServiceException;
use App\Exception\EntityValidationException;
use App\Pagination\Paginator;
use App\Repository\Question\CategoryRepository;
use App\Dto\Question\CategoryForm;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\Question\CategorySearchForm;

/**
 * Сервис для работы с категориями вопросов
 */
final class CategoryService
{
    /**
     * @var CategoryRepository Category Repository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * Конструктор сервиса
     *
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id Идентификатор категории
     * @return Category Получить категорию по её идентификатору
     * @throws ServiceException В случае если категория не найдена
     */
    public function getById(int $id): Category
    {
        $category = $this->categoryRepository->findOneById($id);
        if (empty($category)) {
            throw new ServiceException("Не найдена категория с ID '$id'");
        }

        return $category;
    }

    /**
     * @param string $slug Slug категории
     * @return Category Получить категорию по её slug
     * @throws ServiceException В случае если категория не найдена
     */
    public function getBySlug(string $slug): Category
    {
        $category = $this->categoryRepository->findOneBySlug($slug);
        if (empty($category)) {
            throw new ServiceException("Не найдена категория с SLUG '$slug'");
        }

        return $category;
    }

    /**
     * Создание категории
     *
     * @param CategoryForm $form
     * @return Category Созданная категория
     * @throws EntityValidationException
     */
    public function create(CategoryForm $form): Category
    {
        $category = new Category();
        $category->setStatus(Category::STATUS_ACTIVE);
        $category->setTitle($form->title);
        $category->setSlug($form->slug);
        $category->setHref('');

        return $this->save($category);
    }

    /**
     * Редактирование категории
     *
     * @param int $id Идентификатор категории
     * @param CategoryForm $form
     * @return Category Сохраненная категория
     * @throws EntityValidationException|ServiceException
     */
    public function update(int $id, CategoryForm $form): Category
    {
        $category = $this->getById($id);
        $category->setTitle($form->title);
        $category->setSlug($form->slug);

        return $this->save($category);
    }

    /**
     * Обновить количесто вопросов у категории
     *
     * @param int $id Идентификатор категории
     * @param int $count Количество вопросов
     * @return Category Обновленная категория
     * @throws ServiceException
     */
    public function updateTotalQuestions(int $id, int $count): Category
    {
        $category = $this->getById($id);
        $category->setTotalQuestions($count);

        return $this->save($category);
    }

    /**
     * Листинг категорий с фильтрацией
     *
     * @param CategorySearchForm $form Форма поиска
     * @param int $page Номер страницы
     * @param int $pageSize Количество записей на страницу
     * @return Paginator Результат выборка с постраничным выводом
     * @throws ServiceException
     */
    public function listing(CategorySearchForm $form, $page = 1, $pageSize = 30): Paginator
    {
        if (!empty($form->status)) {
            if (!isset(Category::$statusList[$form->status])) {
                throw new ServiceException("У категорий нет статуса '$form->status'");
            }
        }

        $query = $this->categoryRepository->listingFilter($form);
        return (new Paginator($query, $pageSize))->paginate($page);
    }

    /**
     * Удаление категории
     *
     * @param int $id Идентификатор категории
     * @return Category Удаленная категория
     * @throws ServiceException|EntityValidationException
     */
    public function delete(int $id): Category
    {
        $category = $this->getById($id);
        if ($category->isDeleted()) {
            throw new ServiceException("Категория уже удалена");
        }

        if ($category->getTotalQuestions() > 0) {
            throw new ServiceException("Невозможно удалить категорию, т.к. в ней есть активные вопросы.");
        }

        $category->setStatus(Category::STATUS_DELETED);
        return $this->save($category);
    }

    /**
     * Восстановление категории
     *
     * @param int $id Идентификатор категории
     * @return Category Восстановленная категория
     * @throws ServiceException|EntityValidationException
     */
    public function restore(int $id): Category
    {
        $category = $this->getById($id);
        if ($category->isActive()) {
            throw new ServiceException("Категория уже активна");
        }

        $category->setStatus(Category::STATUS_ACTIVE);
        return $this->save($category);
    }

    /**
     * Процесс сохранения категории
     *
     * @param Category $category Category
     * @return Category Сохраненная категория
     */
    private function save(Category $category): Category
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }
}
