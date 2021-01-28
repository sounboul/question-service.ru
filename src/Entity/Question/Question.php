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
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private Category $category;

    /**
     * @ORM\Column(type="text")
     */
    private string $title;

    /**
     * @ORM\Column(type="text")
     */
    private string $text;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private string $href;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private string $createdByIp;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $totalAnswers = 0;

    /**
     * @return int|null Получить идентификатор категории
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string Получить статус вопроса
     */
    public function getStatus(): string
    {
        return $this->status;
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
     * @return Category Получить категорию
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
     * @return string Получить заголовок
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Установить заголовок
     *
     * @param string $title Заголовок
     * @return self
     * @throws EntityValidationException
     */
    public function setTitle(string $title): self
    {
        $this->title = trim(strip_tags($title));
        if (empty($this->title) || mb_strlen($this->title) < 10) {
            throw new EntityValidationException("Заголовок вопроса должен содержать не менее 10 символов");
        }

        return $this;
    }

    /**
     * @return string Получить текст
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Установить текст
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
     * @return string Получить slug
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
        $this->slug = trim(strip_tags($slug));
        if (empty($this->slug)) {
            throw new EntityValidationException("Передан невалидный slug");
        }

        $this->slug = implode("-", array_slice(explode("-", $this->slug), 0, 6));
        $this->slug = mb_strtolower(mb_substr($this->slug, 0, 150));

        return $this;
    }

    /**
     * @return string Получить href
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * Установить href
     *
     * @param string $href Href
     * @return self
     */
    public function setHref(string $href): self
    {
        $this->href = trim(strip_tags($href));

        return $this;
    }

    /**
     * @return string Получить IP автора вопроса
     */
    public function getCreatedByIp(): string
    {
        return $this->createdByIp;
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
     * @return int Получить количество ответов к вопросу
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
}
