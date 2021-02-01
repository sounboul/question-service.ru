<?php
namespace App\Entity\Question;

use App\Entity\TimestampableEntity;
use App\Exception\EntityValidationException;
use App\Repository\Question\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Question Category Entity
 *
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\Table(
 *     name="`question_category`",
 *     indexes={
 *          @Index(name="question_category_status", columns={"status"})
 *     }
 * )
 * @UniqueEntity(fields={"slug"}, message="Категория с указанным slug уже существует")
 * @ORM\HasLifecycleCallbacks
 */
class Category
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
     * @ORM\Column(
     *     type="text",
     *     nullable=false
     * )
     */
    private string $title;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=200,
     *     unique=true,
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
     *     type="integer",
     *     nullable=false
     * )
     */
    private int $totalQuestions = 0;

    /**
     * @return int Идентификатор категории
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string Статус категории
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
     * Установить статус категории
     *
     * @param string $status Статус категории
     * @return self
     * @throws EntityValidationException
     */
    public function setStatus(string $status): self
    {
        if (!isset(self::$statusList[$status])) {
            throw new EntityValidationException("Некорректный статус для категории: '{$status}'");
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return string Название категории
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Установить название категории
     *
     * @param string $title Название категории
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = trim(strip_tags($title));

        return $this;
    }

    /**
     * @return string Slug категории
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
     * @return string Href категории
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
     * @return int Количество вопросов в категории
     */
    public function getTotalQuestions(): int
    {
        return $this->totalQuestions;
    }

    /**
     * Установить количество вопросов в категории
     *
     * @param int $count Количество вопросов
     * @return self
     */
    public function setTotalQuestions(int $count): self
    {
        $this->totalQuestions = $count;

        return $this;
    }

    /**
     * @return bool Категория активна?
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool Категория удалена?
     */
    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }
}
