<?php
namespace App\Service\User;

use App\Entity\User\User;
use App\Exception\EntityValidationException;
use App\Repository\User\UserRepository;
use App\Exception\ServiceException;
use App\Utils\User\PasswordGenerator;
use App\Utils\User\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сервис для работы с пользователями
 */
class UserService
{
    /**
     * @var UserRepository User Repository
     */
    private UserRepository $userRepository;

    /**
     * @var EntityManagerInterface Entity Manager
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var UserNotification User Notification;
     */
    private UserNotification $userNotification;

    /**
     * @var UserPasswordEncoderInterface Password Encoder
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var UserPhotoService User Photo Service
     */
    private UserPhotoService $userPhotoService;

    /**
     * Конструктор сервиса
     *
     * @param UserRepository $userRepository User Repository
     * @param EntityManagerInterface $entityManager Entity Manager
     * @param UserNotification $userNotification User Notification
     * @param UserPhotoService $userPhotoService User Photo Service
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     */
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserNotification $userNotification,
        UserPhotoService $userPhotoService,

        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userNotification = $userNotification;
        $this->userPhotoService = $userPhotoService;

        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Регистрация пользователя. Классический вариант.
     *
     * @param string $email E-mail адрес пользователя
     * @param string $password Пароль в открытом виде
     * @param bool $sendEmailConfirmation Отправить письмо для подтверждения E-mail адреса?
     * @return User Пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function registration(string $email, string $password, bool $sendEmailConfirmation = true) : User
    {
        $email = trim(mb_strtolower($email));
        $user = $this->userRepository->findOneByEmail($email, false);
        if (!empty($user)) {
            throw new ServiceException("Пользователь с таким E-mail адресом уже существует");
        }

        // инициализация сущности
        $user = new User();
        $user->setUsername(ucfirst(explode('@', $email)[0]));
        $user->setStatus($user::STATUS_ACTIVE);
        $user->setEmail($email);
        $user->setPlainPassword($password, $this->passwordEncoder);

        // сохранение сущности
        $this->updateUser($user);

        // отправка письма для подтверждения E-mail адреса
        if ($sendEmailConfirmation) {
            $this->sendEmailConfirmation($user->getEmail());
        }

        return $user;
    }

    /**
     * Быстрая регистрация пользователя на основе только E-mail адреса.
     * Письмо с подтверждением приходит с указанием пароля.
     *
     * @param string $email E-mail адрес пользователя
     * @param bool $sendEmailConfirmation Отправить письмо для подтверждения E-mail адреса?
     * @return User Пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function fastRegistration(string $email, bool $sendEmailConfirmation = true) : User
    {
        $password = PasswordGenerator::generate();
        $user = $this->registration($email, $password, false);

        // отправка письма для подтверждения E-mail адреса (с паролем)
        if ($sendEmailConfirmation) {
            $this->sendEmailConfirmation($user->getEmail(), $password);
        }

        return $user;
    }

    /**
     * Отправка письма с подтверждением E-mail адреса
     *
     * @param string $email E-mail адрес
     * @param string|null $password Пароль пользователя
     * @return User Пользователь
     * @throws ServiceException
     */
    public function sendEmailConfirmation(string $email, string $password = null): User
    {
        $user = $this->getUserByEmail($email);
        if ($user->getEmailVerified()) {
            throw new ServiceException("У данного пользователя E-mail уже подтвержден");
        }

        // формирование токена для подтверждения (срок действия токена - 5 дней)
        do {
            $token = TokenGenerator::generate(15)."___".strtotime('+5 days');
            $check = $this->userRepository->findOneByEmailVerifiedToken($token);
        } while($check instanceof UserInterface);

        // сохраняем token пользователю
        $user->setEmailVerifiedToken($token);
        $this->updateUser($user);

        // отправка письма пользователю
        $this->userNotification->sendEmail(
            $user,
            'Регистрация на сайте',
            'user/email-confirmation.html.twig',
            compact('token', 'password')
        );

        return $user;
    }

    /**
     * Процессинг подтверждения E-mail адреса
     *
     * @param string $token Token подтверждения
     * @return User Пользователь
     * @throws ServiceException
     */
    public function handleEmailConfirmation(string $token): User
    {
        if (empty($token)) {
            throw new ServiceException("Не указан 'token' для подтверждения e-mail адреса");
        }

        $user = $this->userRepository->findOneByEmailVerifiedToken($token);
        if (empty($user)) {
            throw new ServiceException("Указан невалидный token подтверждения e-mail адреса");
        }

        list($tokenString, $tokenTime) = explode('___', $token);
        if (empty($tokenTime) || $tokenTime < time()) {
            $this->sendEmailConfirmation($user->getEmail());

            throw new ServiceException("Прошёл срок действия указнного token. Мы выслали вам новое письмо, пройдите по ссылке из него.");
        }

        $user->setEmailVerified(true);
        $user->setEmailVerifiedToken(null);

        return $this->updateUser($user);
    }

    /**
     * Отправка письма с подтверждением подписки на E-mail рассылку
     *
     * @param string $email E-mail адрес
     * @return User Пользователь
     * @throws ServiceException
     */
    public function sendEmailSubscribed(string $email): User
    {
        $user = $this->getUserByEmail($email);
        if ($user->getEmailSubscribed()) {
            throw new ServiceException("Данный пользователь уже подписан на E-mail рассылку");
        }

        // формирование токена для подтверждения (срок действия токена - 5 дней)
        do {
            $token = TokenGenerator::generate(15)."___".strtotime('+5 days');
            $check = $this->userRepository->findOneByEmailSubscribedToken($token);
        } while($check instanceof UserInterface);

        // сохраняем token пользователю
        $user->setEmailSubscribedToken($token);
        $this->updateUser($user);

        // отправка письма пользователю
        $this->userNotification->sendEmail(
            $user,
            'Подписка на E-mail рассылку',
            'user/email-subscribed.html.twig',
            compact('token')
        );

        return $user;
    }

    /**
     * Процессинг подтверждения подписки на E-mail рассылку
     *
     * @param string $token Token подтверждения
     * @return User Пользователь
     * @throws ServiceException
     */
    public function handleEmailSubscribed(string $token): User
    {
        if (empty($token)) {
            throw new ServiceException("Не указан 'token' для подтверждения подписки на E-mail рассылку");
        }

        $user = $this->userRepository->findOneByEmailSubscribedToken($token);
        if (empty($user)) {
            throw new ServiceException("Указан невалидный token для подтверждения подписки на E-mail рассылку");
        }

        list($tokenString, $tokenTime) = explode('___', $token);
        if (empty($tokenTime) || $tokenTime < time()) {
            $this->sendEmailSubscribed($user->getEmail());

            throw new ServiceException("Прошёл срок действия указнного token. Мы выслали вам новое письмо, пройдите по ссылке из него.");
        }

        // подписка на E-mail рассылку автоматически подтвержает E-mail адрес
        $user->setEmailVerified(true);
        $user->setEmailVerifiedToken(null);

        // @TODO Формирование токена для отписки на рассылки
        $user->setEmailSubscribed(true);
        $user->setEmailSubscribedToken(null);

        return $this->updateUser($user);
    }

    /**
     * Запрос на восстановление пароля пользователю
     *
     * @param string $email E-mail адрес
     * @return User Пользователь
     * @throws ServiceException
     */
    public function forgotPasswordRequest(string $email): User
    {
        $user = $this->getUserByEmail($email);

        // формирование токена для подтверждения (срок действия токена - 1 день)
        do {
            $token = TokenGenerator::generate(15)."___".strtotime('+1 days');
            $check = $this->userRepository->findOneByPasswordRestoreToken($token);
        } while($check instanceof UserInterface);

        // сохраняем token пользователю
        $user->setPasswordRestoreToken($token);
        $this->updateUser($user);

        // отправка письма пользователю
        $this->userNotification->sendEmail(
            $user,
            'Восстановление пароля',
            'user/forgot-password-request.html.twig',
            compact('token')
        );

        return $user;
    }

    /**
     * Изменение пароля пользователю через систему восстановления пароля
     *
     * @param string $token Password Restore Token
     * @param string $password Новый пароль в открытом виде
     * @return User Пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function resetPassword(string $token, string $password): User
    {
        $user = $this->getUserByPasswordRestoreToken($token);

        $user->setPlainPassword($password, $this->passwordEncoder);
        $user->setPasswordRestoreToken(null);

        return $this->updateUser($user);
    }

    /**
     * Изменение пароля пользователю
     *
     * @param string $email E-mail адрес
     * @param string $password Новый пароль
     * @return User Пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function changePassword(string $email, string $password): User
    {
        $user = $this->getUserByEmail($email);
        $user->setPlainPassword($password, $this->passwordEncoder);

        return $this->updateUser($user);
    }

    /**
     * @param string $token Token для восстановление пароля
     * @return User Получить пользователя по token восстановления пароля
     * @throws ServiceException В случае если пользователь не найден
     */
    public function getUserByPasswordRestoreToken(string $token): User
    {
        if (empty($token)) {
            throw new ServiceException("Не указан 'token' для восстановления пароля");
        }

        $user = $this->userRepository->findOneByPasswordRestoreToken($token);
        if (empty($user)) {
            throw new ServiceException("Указан невалидный token для восстановления пароля");
        }

        list($tokenString, $tokenTime) = explode('___', $token);
        if (empty($tokenTime) || $tokenTime < time()) {
            $this->forgotPasswordRequest($user->getEmail());

            throw new ServiceException("Прошёл срок действия указнного token. Мы выслали вам новое письмо, пройдите по ссылке из него.");
        }

        return $user;
    }

    /**
     * Обновить профиль пользователя
     *
     * @param string $email E-mail пользователя
     * @param string $username Имя пользователя
     * @param string|null $about Информация о пользователе
     * @param UploadedFile|null $photo Загруженый файл аватарки
     * @return User Пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function updateProfile(string $email, string $username, string $about, ?UploadedFile $photo): User
    {
        $user = $this->getUserByEmail($email);

        $user->setUsername($username);
        $user->setAbout($about);

        if (!empty($photo)) {
            $user->setPhoto($this->userPhotoService->uploadPhoto($photo, $user));
        }

        return $this->updateUser($user);
    }

    /**
     * @param string $email E-mail адрес
     * @param bool $isActive Выборка только активного пользователя
     * @return User Получить пользователя по его E-mail адресу
     * @throws ServiceException В случае если пользователь не найден
     */
    public function getUserByEmail(string $email, bool $isActive = true): User
    {
        $email = trim(mb_strtolower($email));
        $user = $this->userRepository->findOneByEmail($email, $isActive);
        if (empty($user)) {
            throw new ServiceException("Не найден пользователь с указанным E-mail адресом");
        }

        return $user;
    }

    /**
     * Процесс сохранения пользователя
     *
     * @param User $user Пользователь для сохранения
     * @return User Сохраненный пользователь
     */
    public function updateUser(User $user): User
    {
        // действия до сохранения пользователя
        $user->updatedTimestamps();

        // сохранение пользователя
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // действия после сохранения пользователя

        return $user;
    }
}
