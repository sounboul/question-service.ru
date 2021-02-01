<?php
namespace App\Repository\Question;

use App\Entity\Question\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Dto\Question\AnswerSearchForm;
use Doctrine\Persistence\ManagerRegistry;
use App\Exception\AppException;
use Doctrine\ORM\QueryBuilder;

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
        return $this->find($id);
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

    /**
     * Листинг ответов с фильтрацией
     *
     * @param AnswerSearchForm $form Форма поиска
     * @return QueryBuilder Список ответов
     * @throws AppException
     */
    public function listingFilter(AnswerSearchForm $form): QueryBuilder
    {
        $query = $this->createQueryBuilder('a');

        // joins
        $query->innerJoin('a.user', 'u');
        $query->innerJoin('a.question', 'q');

        // filters
        if (!empty($form->id)) {
            $query->andWhere('a.id = :id')
                ->setParameter('id', $form->id);
        }

        if (!empty($form->status)) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $form->status);
        }

        if (!empty($form->text)) {
            $query->andWhere('a.text like :text')
                ->setParameter('text', '%'.$form->text.'%');
        }

        if (!empty($form->questionId)) {
            $query->andWhere('a.question = :questionId')
                ->setParameter('questionId', $form->questionId);
        }

        if (!empty($form->userId)) {
            $query->andWhere('a.user = :userId')
                ->setParameter('userId', $form->userId);
        }

        if (!empty($form->createdByIp)) {
            $query->andWhere('a.createdByIp like :createdByIp')
                ->setParameter('createdByIp', '%'.$form->createdByIp.'%');
        }

        // order by
        $availableOrdersBy = [
            'a.id_DESC' => ['a.id' => 'DESC'],
            'a.id_ASC' => ['a.id' => 'ASC'],
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
