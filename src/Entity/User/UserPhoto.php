<?php
namespace App\Entity\User;

use App\Entity\TimestampableEntity;
use App\Exception\EntityValidationException;
use App\Repository\User\UserPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Imagine\Image\ManipulatorInterface;

/**
 * User Photo Entity
 *
 * @ORM\Entity(repositoryClass=UserPhotoRepository::class)
 * @ORM\Table(
 *     name="`user_photo`",
 *     indexes={
 *          @Index(name="user_photo_status", columns={"status"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class UserPhoto
{
    use TimestampableEntity;

    /**
     * @const string Статус "Активен"
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * @const string Статус "Удален"
     */
    public const STATUS_DELETED = 'deleted';

    /**
     * @var array Список статусов
     */
    public static array $statusList = [
        self::STATUS_ACTIVE => 'Активен',
        self::STATUS_DELETED => 'Удалён',
    ];

    /**
     * @var array Список миниатюр сущности
     */
    public static array $thumbnails = [
        // thumbnail
        [
            'key' => 'thumbnail',
            'width' => 400,
            'height' => 400,
            'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND,
            'optimize' => true,
        ],
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private User $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\Column(type="text")
     */
    private string $originalPath;

    /**
     * @ORM\Column(type="text")
     */
    private string $thumbnailPath;

    /**
     * @return int|null Получить идентификатор фотографии
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User Получить пользователя
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Установить пользователя
     *
     * @param User $user User
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string Получить статус фотографии
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Установить статус фотографии
     *
     * @param string $status Статус фотографии
     * @return self
     * @throws EntityValidationException
     */
    public function setStatus(string $status): self
    {
        if (!isset(self::$statusList[$status])) {
            throw new EntityValidationException("Некорректный статус для фотографии: '{$status}'");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return string Получить originalPath
     */
    public function getOriginalPath(): string
    {
        return $this->originalPath;
    }

    /**
     * Установить originalPath
     *
     * @param string $originalPath Original Path
     * @return self
     */
    public function setOriginalPath(string $originalPath): self
    {
        $this->originalPath = $originalPath;

        return $this;
    }

    /**
     * @return string Получить thumbnailPath
     */
    public function getThumbnailPath(): string
    {
        return $this->thumbnailPath;
    }

    /**
     * Установить thumbnailPath
     *
     * @param string $thumbnailPath Thumbnail Path
     * @return self
     */
    public function setThumbnailPath(string $thumbnailPath): self
    {
        $this->thumbnailPath = $thumbnailPath;

        return $this;
    }
}
