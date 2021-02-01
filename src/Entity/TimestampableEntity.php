<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Обеспечивает работу с полями createdAt и updatedAt у Entity
 */
trait TimestampableEntity
{
    /**
     * @var DateTime|null Дата и время создания сущности
     *
     * @ORM\Column(
     *     type="datetime",
     *     nullable=true
     * )
     */
    protected ?DateTime $createdAt = null;

    /**
     * @var DateTime|null Дата и время последнего обновления сущности
     *
     * @ORM\Column(
     *     type="datetime",
     *     nullable=true
     * )
     */
    protected ?DateTime $updatedAt = null;

    /**
     * @return DateTime|null Получить дату и время создания сущности
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Установить дату и время создания сущности
     *
     * @param DateTime $createdAt
     * @return self
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime|null Получить дата и время последнего обновления сущности
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Установить дату и время последнего обновления сущности
     *
     * @param DateTime $updatedAt
     * @return self
     */
    public function setUpdatedAt(DateTime $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Установить дату и время создания и обновления сущности
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps() : void
    {
        $this->setUpdatedAt(new DateTime('now'));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new DateTime('now'));
        }
    }
}
