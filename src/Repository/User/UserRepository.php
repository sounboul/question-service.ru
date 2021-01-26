<?php
namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
