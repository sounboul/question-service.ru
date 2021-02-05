<?php
namespace App\Repository\Question;

use App\Entity\Question\Category;
use App\Exception\AppException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\Question\CategorySearchForm;

/**
 * Question Category Repository
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param int $id Идентификатор
     * @return Category|null Найти категорию по идентификатору
     */
    public function findOneById(int $id): ?Category
    {
        return $this->find($id);
    }

    /**
     * @param string $slug Slug
     * @return Category|null Найти категорию по slug
     */
    public function findOneBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => trim($slug)]);
    }

    /**
     * @return Category[] Список активных категорий
     */
    public function getActiveCategories(): array
    {
        return $this->findBy(['status' => Category::STATUS_ACTIVE], ['title' => 'ASC']);
    }

    /**
     * Листинг категорий с фильтрацией
     *
     * @param CategorySearchForm $form Форма поиска
     * @return QueryBuilder Список категорий
     * @throws AppException
     */
    public function listingFilter(CategorySearchForm $form): QueryBuilder
    {
        $query = $this->createQueryBuilder('c');

        // filters
        if (!empty($form->id)) {
            $query->andWhere('c.id = :id')
                ->setParameter('id', $form->id);
        }

        if (!empty($form->status)) {
            $query->andWhere('c.status = :status')
                ->setParameter('status', $form->status);
        }

        if (!empty($form->title)) {
            $query->andWhere('c.title like :title')
                ->setParameter('title', '%'.$form->title.'%');
        }

        // order by
        $availableOrdersBy = [
            'c.id_DESC' => ['c.id' => 'DESC'],
            'c.id_ASC' => ['c.id' => 'ASC'],

            'c.totalQuestions_DESC' => ['c.totalQuestions' => 'DESC'],
            'c.totalQuestions_ASC' => ['c.totalQuestions' => 'ASC'],
        ];

        if (!empty($form->orderBy)) {
            if (!isset($availableOrdersBy[$form->orderBy])) {
                throw new AppException("Направление сортировки '{$form->orderBy}' не поддерживается");
            }

            foreach ($availableOrdersBy[$form->orderBy] as $key => $value) {
                $query->addOrderBy($key, $value);
            }
        }

        return $query;
    }
}
