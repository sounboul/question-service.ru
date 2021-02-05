<?php
namespace App\Entity\Question;

use App\Entity\TimestampableEntity;
use App\Entity\User\User;
use App\Exception\EntityValidationException;
use App\Repository\Question\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index as Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Answer Entity
 *
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 * @ORM\Table(
 *     name="`question_answer`",
 *     indexes={
 *          @Index(name="question_answer_status", columns={"status"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Answer
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
     *     targetEntity="App\Entity\Question\Question"
     * )
     * @ORM\JoinColumn(
     *     name="question_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private Question $question;

    /**
     * @ORM\Column(
     *     type="text",
     *     nullable=false
     * )
     */
    private string $text;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=46,
     *     nullable=true
     * )
     */
    private ?string $createdByIp = null;

    /**
     * @return int Идентификатор ответа
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string Статус ответа
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
     * Установить статус ответа
     *
     * @param string $status Статус ответа
     * @return self
     * @throws EntityValidationException
     */
    public function setStatus(string $status): self
    {
        if (!isset(self::$statusList[$status])) {
            throw new EntityValidationException("Некорректный статус для ответа: '{$status}'");
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
     * @return Question Вопрос
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * Установить вопрос
     *
     * @param Question $question Вопрос
     * @return self
     */
    public function setQuestion(Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string Текст ответа
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Установить текст
     *
     * @param string $text Текст ответа
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = trim(strip_tags($text));

        return $this;
    }

    /**
     * @return string Получить IP автора ответа
     */
    public function getCreatedByIp(): string
    {
        return (string) $this->createdByIp;
    }

    /**
     * Установить IP автора ответа
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
     * @return bool Ответ активен?
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return bool Ответ удален?
     */
    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }
}
