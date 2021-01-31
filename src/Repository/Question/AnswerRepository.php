<?php
namespace App\Repository\Question;

use App\Entity\Question\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Answer Repository
 *
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * @param int $id Идентификатор
     * @return Answer|null Найти ответ по идентификатору
     */
    public function findOneById(int $id): ?Answer
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param int $questionId Идентификатор вопроса
     * @return int Количество ответов у вопроса
     */
    public function countAnswersByQuestionId(int $questionId): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where("a.status = :status AND a.question = :questionId")
            ->setParameter('status', Answer::STATUS_ACTIVE)
            ->setParameter('questionId', $questionId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
