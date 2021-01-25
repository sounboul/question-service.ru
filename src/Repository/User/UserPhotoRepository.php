<?php
namespace App\Repository\User;

use App\Entity\User\UserPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * User Photo Repository
 *
 * @method UserPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPhoto[]    findAll()
 * @method UserPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPhotoRepository extends ServiceEntityRepository
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPhoto::class);
    }

    /**
     * Подтверждение установки новой фотографии.
     * Всем старым фотографиям пользователя устанавливается статус deleted.
     *
     * @param int $userId Идентификатор пользователя
     * @param int $photoId Идентификатор новой фотографии
     * @return void
     */
    public function confirmNewPhoto(int $userId, int $photoId): void
    {
        $this->_em->createQueryBuilder()
            ->update(UserPhoto::class, 'u')
            ->set('u.status', ':status')
            ->where('u.user = :user AND u.id != :photoId')
            ->setParameter('status', 'deleted')
            ->setParameter('user', $userId)
            ->setParameter('photoId', $photoId)
            ->getQuery()
            ->execute();
    }
}
