<?php
namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * User Repository
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $email E-mail адрес
     * @param bool $isActive Активный пользователь?
     * @return User|null Найти пользователя по e-mail адресу
     */
    public function findOneByEmail(string $email, bool $isActive = true): ?User
    {
        try {
            if ($isActive) {
                $query = $this->getActiveQueryBuilder();
            } else {
                $query = $this->createQueryBuilder('u');
            }

            return $query
                ->andWhere('u.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param string $token Email Verified Token
     * @return User|null Найти пользователя по token подтверждения E-mail адреса
     */
    public function findOneByEmailVerifiedToken(string $token) : ?User
    {
        try {
            return $this->getActiveQueryBuilder()
                ->andWhere('u.emailVerifiedToken = :token')
                ->setParameter('token', $token)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param string $token Email Subscribed Token
     * @return User|null Найти пользователя по token для подписки на E-mail рассылку
     */
    public function findOneByEmailSubscribedToken(string $token) : ?User
    {
        try {
            return $this->getActiveQueryBuilder()
                ->andWhere('u.emailSubscribedToken = :token')
                ->setParameter('token', $token)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param string $token Password Restore Token
     * @return User|null Найти пользователя по token для восстановления пароля
     */
    public function findOneByPasswordRestoreToken(string $token) : ?User
    {
        try {
            return $this->getActiveQueryBuilder()
                ->andWhere('u.passwordRestoreToken = :token')
                ->setParameter('token', $token)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Выборка включает фильтр по только активным пользова
     *
     * @return QueryBuilder Условия выборка сущности по-умолчанию
     */
    protected function getActiveQueryBuilder() : QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.status = :status')
            ->setParameter('status', User::STATUS_ACTIVE);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
