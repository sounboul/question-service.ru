<?php
namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Repository
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Найти пользователя по e-mail адресу
     *
     * @param string $email E-mail адрес
     * @param bool $isActive Активный пользователь?
     * @return User|null
     * @throws NonUniqueResultException
     */
    public function findOneByEmail(string $email, bool $isActive = true): ?User
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        if ($isActive) {
            $query
                ->andWhere('u.status = :status')
                ->setParameter('status', User::STATUS_ACTIVE);
        }

        return $query
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Найти пользователя по token подтверждения E-mail адреса
     *
     * @param string $token Email Verified Token
     * @return User|null Пользователь с указанным токеном
     * @throws NonUniqueResultException
     */
    public function findOneByEmailVerifiedToken(string $token) : ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.emailVerifiedToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Найти пользователя по token для подписки на E-mail рассылку
     *
     * @param string $token Email Subscribed Token
     * @return User|null Пользователь с указанным токеном
     * @throws NonUniqueResultException
     */
    public function findOneByEmailSubscribedToken(string $token) : ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.emailSubscribedToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Найти пользователя по token для восстановления пароля
     *
     * @param string $token Password Restore Token
     * @return User|null Пользователь с указанным токеном
     * @throws NonUniqueResultException
     */
    public function findOneByPasswordRestoreToken(string $token) : ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.passwordRestoreToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);

        $this->_em->persist($user);
        $this->_em->flush();
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
