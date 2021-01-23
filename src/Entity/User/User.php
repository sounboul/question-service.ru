<?php
namespace App\Entity\User;

use App\Entity\TimestampableEntity;
use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User Entity
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`", indexes={@Index(name="status", columns={"status"})})
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"email"}, message="Пользователь с указанным E-mail адресом уже существует")
 */
class User implements UserInterface, EquatableInterface
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
    private ?array $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private ?string $password;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    private ?string $passwordRestoreToken;

    /**
     * @return int|null Получить идентификатор пользователя
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null Получить имя пользователя
     */
    public function getUsername(): ?string
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
        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null Получить статус пользователя
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Установить статус пользователя
     *
     * @param string $status Статус пользователя
     * @return self
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null Получить e-mail пользователя
     */
    public function getEmail(): ?string
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
        $this->email = $email;

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
     */
    public function setRoles(array $roles): self
    {
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
     * @param int $length Длинна пароля
     * @return string Сгенерированный случайный пароль
     * @throws \Exception
     */
    public static function generateRandomPassword(int $length = 10) : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
        $password = '';
        $characterListLength = mb_strlen($characters, '8bit') - 1;

        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }

        return $password;
    }

    /**
     * Используется для проверки того, равны ли два объекта
     * в контексте безопасности и повторной аутентификации.
     *
     * @param UserInterface $user
     * @return bool
     *
     * @see EquatableInterface
     */
    public function isEqualTo(UserInterface $user): bool
    {
        // автоматический logout в случае изменения статуса
        if ($this->getStatus() != $user->getStatus()) {
            return false;
        }

        return true;
    }
}
