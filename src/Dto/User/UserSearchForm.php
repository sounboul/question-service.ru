<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по пользователям
 */
class UserSearchForm
{
    /**
     * @var int Идентификатор
     */
    public int $id;

    /**
     * @var string Имя пользователя
     */
    public string $username;

    /**
     * @var string Статус
     */
    public string $status;

    /**
     * @var string E-mail
     */
    public string $email;

    /**
     * @var string Роль
     */
    public string $role;

    /**
     * @var array Сортировка результатов поиска
     */
    private array $orderBy = [];

    /**
     * @return array Сортировка результатов поиска
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }
}
