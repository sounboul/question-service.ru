<?php
namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\UserPhoto;
use App\Exception\EntityValidationException;
use App\Exception\ServiceException;
use App\Repository\User\UserPhotoRepository;
use App\Service\FileManager\ImageManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Сервис для работы с фотографиями пользователей
 */
class UserPhotoService
{
    /**
     * @var UserPhotoRepository User Photo Repository
     */
    private UserPhotoRepository $userPhotoRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var ImageManagerService Image Manager Service
     */
    private ImageManagerService $imageManager;

    /**
     * @var string Папка для хранения фотографий
     */
    private string $photoFolder = '/user-photo';

    /**
     * Конструктор сервиса
     *
     * @param UserPhotoRepository $userPhotoRepository User Repository
     * @param EntityManagerInterface $entityManager Entity Manager
     * @param ImageManagerService $imageManager Image Manager
     */
    public function __construct(
        UserPhotoRepository $userPhotoRepository,
        EntityManagerInterface $entityManager,
        ImageManagerService $imageManager
    )
    {
        $this->userPhotoRepository = $userPhotoRepository;
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
    }

    /**
     * Загрузка фотографии
     *
     * @param UploadedFile $file Информация о загруженном изображении
     * @param User $user Информация о пользователе
     * @return UserPhoto Сущность фотографии
     * @throws ServiceException|EntityValidationException
     */
    public function uploadPhoto(UploadedFile $file, User $user): UserPhoto
    {
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/pjpeg', 'image/png'])) {
            throw new ServiceException("Формат файла не поддерживается. Используйте JPG/JPEG/PNG.");
        }

        $originalFile = $this->imageManager->uploadFile($file, $this->photoFolder);
        $thumbnails = $this->imageManager->createManyThumbnails($originalFile, UserPhoto::$thumbnails, $this->photoFolder);

        $photo = new UserPhoto();
        $photo->setUser($user);
        $photo->setStatus(UserPhoto::STATUS_ACTIVE);
        $photo->setOriginalPath($originalFile);
        $photo->setThumbnailPath($thumbnails['thumbnail']);

        return $this->updatePhoto($photo);
    }

    /**
     * Процесс сохранения фотографии пользователя
     *
     * @param UserPhoto $photo Фотография для сохранения
     * @return UserPhoto Сохраненная фотография
     */
    public function updatePhoto(UserPhoto $photo): UserPhoto
    {
        // действия до сохранения фотографии
        $photo->updatedTimestamps();

        // сохранение фотографии
        $this->entityManager->persist($photo);
        $this->entityManager->flush();

        // действия после сохранения фотографии

        // подтверждение установки новой фотографии
        $this->userPhotoRepository->confirmNewPhoto($photo->getUser()->getId(), $photo->getId());

        return $photo;
    }
}
