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
     * @param int $id Идентификатор
     * @return UserPhoto|null Найти фотографию по идентификатору
     */
    public function findOneById(int $id): ?UserPhoto
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Переключает фотографию пользователю.
     * Всем старым фотографиям пользователя устанавливается статус deleted.
     *
     * @param int $userId Идентификатор пользователя
     * @param int $photoId Идентификатор новой фотографии
     * @return void
     */
    public function switchUserPhoto(int $userId, int $photoId): void
    {
        $this->_em->createQueryBuilder()
            ->update(UserPhoto::class, 'up')
            ->set('up.status', ':status')
            ->where('up.user = :user AND up.id != :photoId')
            ->setParameter('status', 'deleted')
            ->setParameter('user', $userId)
            ->setParameter('photoId', $photoId)
            ->getQuery()
            ->execute();
    }
}
