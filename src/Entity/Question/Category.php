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
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\Column(type="text")
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=200, unique=true)
     */
    private string $slug;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private string $href;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $totalQuestions = 0;

    /**
     * @return int|null Получить идентификатор категории
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string Получить статус категории
     */
    public function getStatus(): string
    {
        return $this->status;
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
     * @return string Получить название категории
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
     * @return int Получить количество вопросов в категории
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
}
