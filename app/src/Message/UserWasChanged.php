<?php
namespace App\Message;

/**
 * Сообщение: Пользователь был изменен
 */
class UserWasChanged
{
    /**
     * @var int User Id
     */
    private int $userId;

    /**
     * @var array Fields
     */
    private array $fields;

    /**
     * Конструктор
     *
     * @param int $userId User Id
     * @param array $fields Fields
     */
    public function __construct(int $userId, array $fields)
    {
        $this->userId = $userId;
        $this->fields = $fields;
    }

    /**
     * @return int User Id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return array Fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
