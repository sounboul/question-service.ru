<?php
namespace App\Service\User;

use App\Entity\User\User;
use App\Exception\ServiceException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User Provider. Используется системой авторизации.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserService User Service
     */
    private UserService $userService;

    /**
     * Конструктор
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        try {
            $user = $this->userService->getUserByEmail($username);
        } catch (ServiceException $e) {
            throw new UsernameNotFoundException($e->getMessage());
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw  new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported. ', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getEmail());
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
