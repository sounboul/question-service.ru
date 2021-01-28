<?php
namespace App\Entity\User;

use App\Entity\TimestampableEntity;
use App\Exception\EntityValidationException;
use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Entity
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(
 *     name="`user`",
 *     indexes={
 *          @Index(name="user_status", columns={"status"})
 *     }
 * )
 * @UniqueEntity(fields={"email"}, message="Пользователь с указанным E-mail адресом уже существует")
 */
class User implements UserInterface
{
    use TimestampableEntity;

    /**
     * @const string Роль "Пользователь"
     */
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @const string Роль "Администратор"
     */
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @const string Статус "Активен"
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * @const string Статус "Заблокирован"
     */
    public const STATUS_BLOCKED = 'blocked';

    /**
     * @const string Статус "Удален"
     */
    public const STATUS_DELETED = 'deleted';

    /**
     * @return array Список ролей
     */
    public static array $roleList = [
        self::ROLE_USER => 'Пользователь',
        self::ROLE_ADMIN => 'Администратор',
    ];

    /**
     * @return array Список статусов
     */
    public static array $statusList = [
        self::STATUS_ACTIVE => 'Активен',
        self::STATUS_BLOCKED => 'Заблокирован',
        self::STATUS_DELETED => 'Удалён',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private ?string $username;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private ?string $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $emailVerified = false;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    private ?string $emailVerifiedToken = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $emailSubscribed = false;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    private ?string $emailSubscribedToken = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    private ?string $passwordRestoreToken;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $about;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\UserPhoto")
     * @ORM\JoinColumn(name="photo_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserPhoto $photo;

    /**
     * @return int|null Получить идентификатор пользователя
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string Получить имя пользователя
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Установить имя пользователя
     *
     * @param string $username Имя пользователя
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = trim(strip_tags($username));

        return $this;
    }

    /**
     * @return string Получить статус пользователя
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string Получить статус пользователя в виде текста
     */
    public function getStatusAsText(): string
    {
        return self::$statusList[$this->status] ?? $this->status;
    }

    /**
     * Установить статус пользователя
     *
     * @param string $status Статус пользователя
     * @return self
     * @throws EntityValidationException
     */
    public function setStatus(string $status): self
    {
        if (!isset(self::$statusList[$status])) {
            throw new EntityValidationException("Некорректный статус для пользователя: '{$status}'");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return string Получить e-mail пользователя
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Установить E-mail пользователя
     *
     * @param string $email E-mail
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower($email);

        return $this;
    }

    /**
     * @return bool Пользователь подтвердил свой E-mail адрес?
     */
    public function getEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    /**
     * Установить Подтвержение E-mail пользователя
     *
     * @param bool $emailVerified
     * @return self
     */
    public function setEmailVerified(bool $emailVerified): self
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * @return string|null Получить token подтверждения e-mail адреса
     */
    public function getEmailVerifiedToken(): ?string
    {
        return $this->emailVerifiedToken;
    }

    /**
     * Установить token подтверждения e-mail адреса
     *
     * @param string|null $emailVerifiedToken
     * @return self
     */
    public function setEmailVerifiedToken(?string $emailVerifiedToken): self
    {
        $this->emailVerifiedToken = $emailVerifiedToken;

        return $this;
    }

    /**
     * @return bool Пользователь подписан на E-mail рассылку?
     */
    public function getEmailSubscribed(): bool
    {
        return $this->emailSubscribed;
    }

    /**
     * Установить Подписку пользователя на E-mail рассылку
     *
     * @param bool $emailSubscribed
     * @return self
     */
    public function setEmailSubscribed(bool $emailSubscribed): self
    {
        $this->emailSubscribed = $emailSubscribed;

        return $this;
    }

    /**
     * @return string|null Получить token подтверждения e-mail адреса
     */
    public function getEmailSubscribedToken(): ?string
    {
        return $this->emailSubscribedToken;
    }

    /**
     * Установить token подтверждения e-mail адреса
     *
     * @param string|null $emailSubscribedToken
     * @return self
     */
    public function setEmailSubscribedToken(?string $emailSubscribedToken): self
    {
        $this->emailSubscribedToken = $emailSubscribedToken;

        return $this;
    }

    /**
     * @return array Получить список ролей пользователя
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * Установить роли пользователя
     *
     * @param array $roles Список ролей
     * @return self
     * @throws EntityValidationException
     */
    public function setRoles(array $roles): self
    {
        array_map(function (string $role) {
            if (!isset(self::$roleList[$role])) {
                throw new EntityValidationException("Некорректная роль для пользователя: '{$role}'");
            }
        }, $roles);

        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string Получить пароль пользователя
     *
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * Установить пароль пользователя
     *
     * @param string $password Новый пароль
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null Получить token для восстановления пароля
     */
    public function getPasswordRestoreToken(): ?string
    {
        return $this->passwordRestoreToken;
    }

    /**
     * Установить token для восстановления пароля
     *
     * @param string|null $passwordRestoreToken
     * @return self
     */
    public function setPasswordRestoreToken(?string $passwordRestoreToken): self
    {
        $this->passwordRestoreToken = $passwordRestoreToken;

        return $this;
    }

    /**
     * @return string|null Получить описание
     */
    public function getAbout(): ?string
    {
        return $this->about;
    }

    /**
     * Установить описание
     *
     * @param string|null $about Описание
     * @return self
     */
    public function setAbout(?string $about): self
    {
        $this->about = trim(strip_tags($about));

        return $this;
    }

    /**
     * @return UserPhoto|null Получить фото
     */
    public function getPhoto(): ?UserPhoto
    {
        return $this->photo;
    }

    /**
     * Установить фото
     *
     * @param UserPhoto|null $photo Фотография
     * @return self
     */
    public function setPhoto(?UserPhoto $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Установить пароль пользователю
     *
     * @param string $password Пароль в открытом виде
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     * @return void
     * @throws EntityValidationException
     */
    public function setPlainPassword(string $password, UserPasswordEncoderInterface $passwordEncoder) : void
    {
        $password = trim($password);
        if (mb_strlen($password) < 8) {
            throw new EntityValidationException("Пароль пользователя должен состоять минимум из 8 символов");
        }

        $this->setPassword($passwordEncoder->encodePassword($this, $password));
    }

    /**
     * @return bool Пользователь активен?
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool Пользователь удален?
     */
    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }

    /**
     * @return bool Пользователь заблокирован?
     */
    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }
}
