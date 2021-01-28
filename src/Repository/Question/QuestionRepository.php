<?php
namespace App\Repository\Question;

use App\Entity\Question\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Question Repository
 *
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @param int $id Идентификатор
     * @return Question|null Найти вопрос по идентификатору
     */
    public function findOneById(int $id): ?Question
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param int $categoryId Идентификатор категории
     * @return int Количество вопросов в указанной категории
     */
    public function countQuestionsByCategoryId(int $categoryId): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where("u.status = :status AND u.category = :categoryId")
            ->setParameter('status', Question::STATUS_ACTIVE)
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
