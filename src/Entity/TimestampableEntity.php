<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Обеспечивает работу с полями createdAt и updatedAt у Entity
 */
trait TimestampableEntity
{
    /**
     * @var \DateTime|null Created At
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime|null Updated At
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return \DateTime|null Получить дату и время создания сущности
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Установить дату и время создания сущности
     *
     * @param \DateTime|null $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Получить дату и время изменения сущности
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Установить дату и время изменения сущности
     *
     * @param \DateTime|null $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Установить дату и время создания и обновления сущности
     */
    public function updatedTimestamps() : void
    {
        $this->setUpdatedAt(new \DateTime('now'));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }
}
