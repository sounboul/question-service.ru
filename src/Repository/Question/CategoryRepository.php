<?php
namespace App\Repository\Question;

use App\Entity\Question\Category;
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
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param string $slug Slug
     * @return Category|null Найти категорию по slug
     */
    public function findOneBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Листинг категорий с фильтрацией
     *
     * @param CategorySearchForm $form Форма поиска
     * @return QueryBuilder Список категорий
     */
    public function listingFilter(CategorySearchForm $form): QueryBuilder
    {
        $query = $this->createQueryBuilder('u');

        // filters
        if (!empty($form->id)) {
            $query->andWhere('u.id = :id')
                ->setParameter('id', $form->id);
        }

        if (!empty($form->status)) {
            $query->andWhere('u.status = :status')
                ->setParameter('status', $form->status);
        }

        if (!empty($form->title)) {
            $query->andWhere('u.title like :title')
                ->setParameter('title', '%'.$form->title.'%');
        }

        // order by
        if (!empty($form->getOrderBy())) {
            foreach ($form->getOrderBy() as $key => $value) {
                $query->addOrderBy($key, $value);
            }
        }

        return $query;
    }
}
