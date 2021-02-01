<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * DTO для редактирования профиля пользователя
 */
class ProfileForm
{
    /**
     * @var string Имя пользователя
     *
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     */
    public string $username;

    /**
     * @var UploadedFile|null Фотография
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     minWidth=400,
     *     minHeight=400,
     *     maxWidth=3000,
     *     maxHeight=3000
     * )
     */
    public ?UploadedFile $photo = null;

    /**
     * @var string|null О себе
     *
     * @Assert\Type("string")
     * @Assert\Length(
     *     max=3000
     * )
     */
    public ?string $about = null;
}
