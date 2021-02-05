<?php
namespace App\Entity\Question;

use App\Entity\TimestampableEntity;
use App\Entity\User\User;
use App\Exception\EntityValidationException;
use App\Repository\Question\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Question Entity
 *
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 * @ORM\Table(
 *     name="`question`",
 *     indexes={
 *          @Index(name="question_status", columns={"status"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Question
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
     * @return array Список статусов
     */
    public static array $statusList = [
        self::STATUS_ACTIVE => 'Активен',
        self::STATUS_DELETED => 'Удалён',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(
     *     name="id",
     *     type="integer",
     *     nullable=false
     * )
     */
    private int $id;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=20,
     *     nullable=false
     * )
     */
    private string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\User"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private ?User $user = null;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Question\Category"
     * )
     * @ORM\JoinColumn(
     *     name="category_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private Category $category;

    /**
     * @ORM\Column(
     *     type="text",
     *     nullable=false
     * )
     */
    private string $title;

    /**
     * @ORM\Column(
     *     type="text",
     *     nullable=true
     * )
     */
    private ?string $text = null;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=200,
     *     nullable=false
     * )
     */
    private string $slug;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=250,
     *     nullable=true
     * )
     */
    private ?string $href = null;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=46,
     *     nullable=true
     * )
     */
    private ?string $createdByIp = null;

    /**
     * @ORM\Column(
     *     type="integer",
     *     nullable=false
     * )
     */
    private int $totalAnswers = 0;

    /**
     * @return int Идентификатор вопроса
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string Статус вопроса
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string Статус в виде текста
     */
    public function getStatusAsText(): string
    {
        return self::$statusList[$this->status] ?? $this->status;
    }

    /**
     * @return array Список возможных статусов
     */
    public static function getStatusList(): array
    {
        return array_flip(self::$statusList);
    }

    /**
     * Установить статус вопроса
     *
     * @param string $status Статус вопроса
     * @return self
     * @throws EntityValidationException
     */
    public function setStatus(string $status): self
    {
        if (!isset(self::$statusList[$status])) {
            throw new EntityValidationException("Некорректный статус для вопроса: '{$status}'");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return User|null Пользователь
     */
    public function getUser(): ?User
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
     * @return Category Категория
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * Установить категорию
     *
     * @param Category $category Category
     * @return self
     */
    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string Название вопроса
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Установить название вопроса
     *
     * @param string $title Название
     * @return self
     * @throws EntityValidationException
     */
    public function setTitle(string $title): self
    {
        $this->title = trim(strip_tags($title));
        if (empty($this->title) || mb_strlen($this->title) < 15) {
            throw new EntityValidationException("Вопрос должен содержать не менее 15 символов");
        }

        return $this;
    }

    /**
     * @return string Текст вопроса
     */
    public function getText(): string
    {
        return (string) $this->text;
    }

    /**
     * Установить текст вопроса
     *
     * @param string $text Текст
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = trim(strip_tags($text));

        return $this;
    }

    /**
     * @return string Slug вопроса
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Установить slug
     *
     * @param string $slug Slug
     * @return self
     * @throws EntityValidationException
     */
    public function setSlug(string $slug): self
    {
        if (empty($slug) || !preg_match('/^[-_\w]+$/isU', $slug)) {
            throw new EntityValidationException("Передан невалидный slug '$slug'");
        }

        $this->slug = (string) mb_strtolower(mb_substr($slug, 0, 200));

        return $this;
    }

    /**
     * @return string Href вопроса
     */
    public function getHref(): string
    {
        return (string) $this->href;
    }

    /**
     * Установить href
     *
     * @param string $href Href
     * @return self
     */
    public function setHref(string $href): self
    {
        $this->href = $href;

        return $this;
    }

    /**
     * @return string IP автора вопроса
     */
    public function getCreatedByIp(): string
    {
        return (string) $this->createdByIp;
    }

    /**
     * Установить IP автора вопроса
     *
     * @param string $ip IP
     * @return self
     */
    public function setCreatedByIp(string $ip): self
    {
        $this->createdByIp = $ip;

        return $this;
    }

    /**
     * @return int Количество ответов к вопросу
     */
    public function getTotalAnswers(): int
    {
        return $this->totalAnswers;
    }

    /**
     * Установить количество ответов к вопросу
     *
     * @param int $count Количество ответов
     * @return self
     */
    public function setTotalAnswers(int $count): self
    {
        $this->totalAnswers = $count;

        return $this;
    }

    /**
     * @return bool Вопрос активен?
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool Вопрос удален?
     */
    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }
}
