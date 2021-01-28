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
     * @ORM\ManyToOne(targetEntity="App\Entity\Question\Question")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     */
    private Question $question;

    /**
     * @ORM\Column(type="text")
     */
    private string $text;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private string $createdByIp;

    /**
     * @return int|null Получить идентификатор категории
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string Получить статус ответа
     */
    public function getStatus(): string
    {
        return $this->status;
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
     * @return Question Получить вопрос
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * Установить вопрос
     *
     * @param Question $question Question
     * @return self
     */
    public function setQuestion(Question $question): self
    {
        $this->question = $question;

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
     * @return string Получить IP автора ответа
     */
    public function getCreatedByIp(): string
    {
        return $this->createdByIp;
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
}
