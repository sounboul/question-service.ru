<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO для поиска с фильстрацией по пользователям
 */
class UserSearchForm
{
    /**
     * @var int|null Идентификатор
     */
    public ?int $id;

    /**
     * @var string|null Имя пользователя
     */
    public ?string $username;

    /**
     * @var string|null Статус
     *
     * @Assert\Choice(
     *     callback={"App\Entity\User\User", "getStatusList"}
     * )
     */
    public ?string $status;

    /**
     * @var string|null E-mail
     */
    public ?string $email;

    /**
     * @var bool|null E-mail подтвержден
     */
    public ?bool $emailVerified = null;

    /**
     * @var bool|null E-mail подписан на рассылку
     */
    public ?bool $emailSubscribed = null;

    /**
     * @var string|null Роль
     *
     * @Assert\Choice(
     *     callback={"App\Entity\User\User", "getRolesList"}
     * )
     */
    public ?string $role;

    /**
     * @var bool|null С фотографией
     */
    public ?bool $withPhoto = null;

    /**
     * @var string|null Сортировка
     */
    public ?string $orderBy;

    /**
     * @return array Доступные варианты сортировки
     */
    public static function getAvailableOrderBy(): array
    {
        return [
            'u.id_DESC' => 'ID, DESC',
            'u.id_ASC' => 'ID, ASC',
        ];
    }
}
