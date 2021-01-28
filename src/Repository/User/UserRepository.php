<?php
namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @param int $id Идентификатор
     * @param bool $isActive Активный пользователь?
     * @return User|null Найти пользователя по идентификатору
     */
    public function findOneById(int $id, bool $isActive = true): ?User
    {
        $criteria = $isActive ? ['status' => 'active'] : [];
        $criteria['id'] = $id;

        return $this->findOneBy($criteria);
    }

    /**
     * @param string $email E-mail адрес
     * @param bool $isActive Активный пользователь?
     * @return User|null Найти пользователя по e-mail адресу
     */
    public function findOneByEmail(string $email, bool $isActive = true): ?User
    {
        $criteria = $isActive ? ['status' => 'active'] : [];
        $criteria['email'] = $email;

        return $this->findOneBy($criteria);
    }

    /**
     * @param string $token Email Verified Token
     * @return User|null Найти пользователя по token подтверждения E-mail адреса
     */
    public function findOneByEmailVerifiedToken(string $token) : ?User
    {
        return $this->findOneBy(['status' => 'active', 'emailVerifiedToken' => $token]);
    }

    /**
     * @param string $token Email Subscribed Token
     * @return User|null Найти пользователя по token для подписки на E-mail рассылку
     */
    public function findOneByEmailSubscribedToken(string $token) : ?User
    {
        return $this->findOneBy(['status' => 'active', 'emailSubscribedToken' => $token]);
    }

    /**
     * @param string $token Password Restore Token
     * @return User|null Найти пользователя по token для восстановления пароля
     */
    public function findOneByPasswordRestoreToken(string $token) : ?User
    {
        return $this->findOneBy(['status' => 'active', 'passwordRestoreToken' => $token]);
    }

    /**
     * Листинг пользователей с фильтрацией
     *
     * @param array $filters Критерии фильтрации пользователей
     * @param array $orderBy Критерии сортировки
     * @return QueryBuilder Список пользователей
     */
    public function listingFilter(array $filters, array $orderBy = []): QueryBuilder
    {
        $query = $this->createQueryBuilder('u');

        // filters
        if (!empty($filters['id'])) {
            $query->andWhere('u.id = :id')
                ->setParameter('id', (int) $filters['id']);
        }

        if (!empty($filters['username'])) {
            $query->andWhere('u.username like :username')
                ->setParameter('username', '%'.$filters['username'].'%');
        }

        if (!empty($filters['status'])) {
            $query->andWhere('u.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['email'])) {
            $query->andWhere('u.email like :email')
                ->setParameter('email', '%'.$filters['email'].'%');
        }

        if (!empty($filters['role'])) {
            // роль пользователь есть у всех
            /*if ($filters['role'] !== User::ROLE_USER) {
                $query->andWhere("JSONB_CONTAINS(u.roles, :role)")
                    ->setParameter('role', $filters['role']);
            }*/
        }

        // order by
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $value) {
                $query->addOrderBy($key, $value);
            }
        }

        return $query;
    }
}
