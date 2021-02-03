<?php
namespace App\Repository\Question;

use App\Dto\Question\QuestionSearchForm;
use App\Entity\Question\Question;
use App\Exception\AppException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
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
        return $this->find($id);
    }

    /**
     * @param int $categoryId Идентификатор категории
     * @return int Количество вопросов в указанной категории
     */
    public function countQuestionsByCategoryId(int $categoryId): int
    {
        return $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where("q.status = :status AND q.category = :categoryId")
            ->setParameter('status', Question::STATUS_ACTIVE)
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Листинг вопросов с фильтрацией
     *
     * @param QuestionSearchForm $form Форма поиска
     * @return QueryBuilder Список вопросов
     * @throws AppException
     */
    public function listingFilter(QuestionSearchForm $form): QueryBuilder
    {
        $query = $this->createQueryBuilder('q');

        // joins
        $query->innerJoin('q.user', 'u');
        $query->innerJoin('q.category', 'c');

        // filters
        if (!empty($form->id)) {
            $query->andWhere('q.id = :id')
                ->setParameter('id', $form->id);
        }

        if (!empty($form->status)) {
            $query->andWhere('q.status = :status')
                ->setParameter('status', $form->status);
        }

        if (!empty($form->text)) {
            $query->andWhere('q.title like :title OR q.text like :text')
                ->setParameter('title', '%'.$form->text.'%')
                ->setParameter('text', '%'.$form->text.'%');
        }

        if (!empty($form->categoryId)) {
            $query->andWhere('q.category = :categoryId')
                ->setParameter('categoryId', $form->categoryId);
        }

        if (!empty($form->userId)) {
            $query->andWhere('q.user = :userId')
                ->setParameter('userId', $form->userId);
        }

        if (!empty($form->createdByIp)) {
            $query->andWhere('q.createdByIp like :createdByIp')
                ->setParameter('createdByIp', '%'.$form->createdByIp.'%');
        }

        if ($form->withoutAnswers === true) {
            $query->andWhere('q.totalAnswers = :totalAnswers')
                ->setParameter('totalAnswers', 0);
        }

        // order by
        $availableOrdersBy = [
            'q.id_DESC' => ['q.id' => 'DESC'],
            'q.id_ASC' => ['q.id' => 'ASC'],

            'q.totalAnswers_DESC' => ['q.totalAnswers' => 'DESC'],
            'q.totalAnswers_ASC' => ['q.totalAnswers' => 'ASC'],
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

    /**
     * @return int Количество вопросов для индексации поисковым движком ElasticSearch
     */
    public function countListingForElastic(): int
    {
        $query = $this->createQueryBuilder('q')
            ->select('count(q.id)');

        // только активные вопросы
        $query->andWhere('q.status = :status')
            ->setParameter('status', Question::STATUS_ACTIVE);

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return iterable Листинг вопросов для индексации поисковым движком ElasticSearch
     */
    public function listingForElastic(): iterable
    {
        $query = $this->createQueryBuilder('q');

        // только активные вопросы
        $query->andWhere('q.status = :status')
            ->setParameter('status', Question::STATUS_ACTIVE);

        return $query->getQuery()->toIterable();
    }
}
