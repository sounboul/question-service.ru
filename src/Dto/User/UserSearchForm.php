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
     *
     * @Assert\Choice(
     *     callback={"App\Entity\User\User", "getStatusList"}
     * )
     */
    public string $status;

    /**
     * @var string E-mail
     */
    public string $email;

    /**
     * @var bool E-mail подтвержден
     */
    public ?bool $emailVerified = null;

    /**
     * @var bool E-mail подписан на рассылку
     */
    public ?bool $emailSubscribed = null;

    /**
     * @var string Роль
     *
     * @Assert\Choice(
     *     callback={"App\Entity\User\User", "getRolesList"}
     * )
     */
    public string $role;

    /**
     * @var bool С фотографией
     */
    public ?bool $withPhoto = null;

    /**
     * @var string Сортировка
     */
    public string $orderBy;

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
