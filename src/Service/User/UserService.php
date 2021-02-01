<?php
namespace App\Service\User;

use App\Entity\User\User;
use App\Exception\EntityValidationException;
use App\Pagination\Paginator;
use App\Repository\User\UserRepository;
use App\Exception\ServiceException;
use App\Utils\User\PasswordGenerator;
use App\Utils\User\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Dto\User\UserRegistrationForm;
use App\Dto\User\UserFastRegistrationForm;
use App\Dto\User\UserUpdateForm;
use App\Dto\User\UserUpdateProfileForm;
use App\Dto\User\UserSearchForm;
use App\Dto\User\UserCreateForm;
use App\Dto\User\UserChangePasswordForm;
use App\Dto\User\UserResetPasswordRequestForm;
use App\Dto\User\UserResetPasswordForm;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface Validator Interface
     */
    private ValidatorInterface $validator;

    /**
     * Конструктор сервиса
     *
     * @param UserRepository $userRepository User Repository
     * @param EntityManagerInterface $entityManager Entity Manager
     * @param UserNotification $userNotification User Notification
     * @param UserPhotoService $userPhotoService User Photo Service
     * @param UserPasswordEncoderInterface $passwordEncoder Password Encoder
     * @param ValidatorInterface $validator Validator
     */
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserNotification $userNotification,
        UserPhotoService $userPhotoService,

        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->userNotification = $userNotification;
        $this->userPhotoService = $userPhotoService;

        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }

    /**
     * @param int $id Идентификатор пользователя
     * @param bool $isActive Выборка только активного пользователя
     * @return User Получить пользователя по его идентификатору
     * @throws ServiceException В случае если пользователь не найден
     */
    public function getUserById(int $id, bool $isActive = true): User
    {
        $user = $this->userRepository->findOneById($id, $isActive);
        if (empty($user)) {
            throw new ServiceException("Не найден пользователь с ID '$id'");
        }

        return $user;
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
            throw new ServiceException("Не найден пользователь с e-mail '$email'");
        }

        return $user;
    }

    /**
     * Создание пользователя
     *
     * @param UserCreateForm $form Форма
     * @return User Созданный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function create(UserCreateForm $form): User
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы UserCreateForm");
        }

        $email = trim(mb_strtolower($form->email));
        $user = $this->userRepository->findOneByEmail($email, false);
        if (!empty($user)) {
            throw new ServiceException("Пользователь с таким E-mail адресом уже существует");
        }

        $user = new User();
        $user->setUsername($form->username);
        $user->setStatus($user::STATUS_ACTIVE);
        $user->setEmail($email);
        $user->setPlainPassword($form->password, $this->passwordEncoder);
        $user->setRoles($form->roles);
        $user->setAbout((string) $form->about);
        $user = $this->save($user);

        if (!empty($form->photo)) {
            $user->setPhoto($this->userPhotoService->uploadPhoto($form->photo, $user));
        }

        return $user;
    }

    /**
     * Регистрация пользователя. Классический вариант.
     *
     * @param UserRegistrationForm $form Форма регистрации
     * @param bool $sendEmailConfirmation Отправить письмо для подтверждения E-mail адреса?
     * @return User Зарегистрированный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function registration(UserRegistrationForm $form, bool $sendEmailConfirmation = true): User
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы UserRegistrationForm");
        }

        $email = trim(mb_strtolower($form->email));

        $formData = new UserCreateForm();
        $formData->username = ucfirst(explode('@', $email)[0]);
        $formData->email = $email;
        $formData->password = $form->password;

        $user = $this->create($formData);

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
     * @param UserFastRegistrationForm $form Форма регистрации
     * @param bool $sendEmailConfirmation Отправить письмо для подтверждения E-mail адреса?
     * @return User Зарегистрированный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function fastRegistration(UserFastRegistrationForm $form, bool $sendEmailConfirmation = true): User
    {
        $formData = new UserRegistrationForm();
        $formData->email = $form->email;
        $formData->password = PasswordGenerator::generate();
        $formData->agreeTerms = true;

        $user = $this->registration($formData, false);

        // отправка письма для подтверждения E-mail адреса (с паролем)
        if ($sendEmailConfirmation) {
            $this->sendEmailConfirmation($user->getEmail(), $formData->password);
        }

        return $user;
    }

    /**
     * Обновить пользователя
     *
     * @param UserUpdateForm $form Форма
     * @return User Обновленный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function update(UserUpdateForm $form): User
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы UserUpdateForm");
        }

        $user = $this->getUserById($form->id);
        $email = mb_strtolower($form->email);
        if ($user->getEmail() !== $email) {
            $checkEmail = $this->userRepository->findOneByEmail($email, false);
            if (!empty($checkEmail)) {
                throw new ServiceException("E-mail адрес '$email' уже используется другим пользователем");
            }
        }

        $user->setUsername($form->username);
        $user->setEmail($email);
        $user->setRoles($form->roles);
        $user->setAbout((string) $form->about);

        if (!empty($form->photo)) {
            $user->setPhoto($this->userPhotoService->uploadPhoto($form->photo, $user));
        }

        return $this->save($user);
    }

    /**
     * Обновить профиль пользователя
     *
     * @param UserUpdateProfileForm $form Форма
     * @return User Обновленный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function updateProfile(UserUpdateProfileForm $form): User
    {
        $user = $this->getUserById($form->id);

        $user->setUsername($form->username);
        $user->setAbout((string) $form->about);

        if (!empty($form->photo)) {
            $user->setPhoto($this->userPhotoService->uploadPhoto($form->photo, $user));
        }

        return $this->save($user);
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
        $this->save($user);

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

        return $this->save($user);
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
        $this->save($user);

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

        $user->setEmailSubscribed(true);
        $user->setEmailSubscribedToken(null);

        return $this->save($user);
    }

    /**
     * Запрос на восстановление пароля пользователю
     *
     * @param UserResetPasswordRequestForm $form
     * @return User Пользователь
     * @throws ServiceException
     */
    public function forgotPasswordRequest(UserResetPasswordRequestForm $form): User
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы UserResetPasswordRequestForm");
        }

        $user = $this->getUserByEmail($form->email);

        // формирование токена для подтверждения (срок действия токена - 1 день)
        do {
            $token = TokenGenerator::generate(15)."___".strtotime('+1 days');
            $check = $this->userRepository->findOneByPasswordRestoreToken($token);
        } while($check instanceof UserInterface);

        // сохраняем token пользователю
        $user->setPasswordRestoreToken($token);
        $this->save($user);

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
     * @param UserResetPasswordForm $form
     * @return User Пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function resetPassword(UserResetPasswordForm $form): User
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы UserResetPasswordForm");
        }

        $user = $this->getUserByPasswordRestoreToken($form->token);

        $user->setPlainPassword($form->password, $this->passwordEncoder);
        $user->setPasswordRestoreToken(null);

        return $this->save($user);
    }

    /**
     * Изменение пароля пользователю
     *
     * @param UserChangePasswordForm $form
     * @return User Обновленный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function changePassword(UserChangePasswordForm $form): User
    {
        if (count($this->validator->validate($form)) > 0) {
            throw new ServiceException("Ошибка валидации формы UserChangePasswordForm");
        }

        $user = $this->getUserById($form->id);
        $user->setPlainPassword($form->password, $this->passwordEncoder);

        return $this->save($user);
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
            $formData = new UserResetPasswordRequestForm();
            $formData->email = $user->getEmail();
            $this->forgotPasswordRequest($formData);

            throw new ServiceException("Прошёл срок действия указнного token. Мы выслали вам новое письмо, пройдите по ссылке из него.");
        }

        return $user;
    }

    /**
     * Листинг пользователей с фильтрацией
     *
     * @param UserSearchForm $form Форма поиска
     * @param int $page Номер страницы
     * @param int $pageSize Количество записей на страницу
     * @return Paginator Результат выборка с постраничным выводом
     * @throws ServiceException
     */
    public function listing(UserSearchForm $form, $page = 1, $pageSize = 30): Paginator
    {
        try {
            $query = $this->userRepository->listingFilter($form);
            return (new Paginator($query, $pageSize))->paginate($page);
        } catch (\Exception $e) {
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * Удаление пользователя
     *
     * @param int $id Идентификатор пользователя
     * @return User Удаленный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function delete(int $id): User
    {
        $user = $this->getUserById($id, false);
        if ($user->isDeleted()) {
            throw new ServiceException("Указанный пользователь уже был удален ранее.");
        }

        $user->setStatus(User::STATUS_DELETED);

        return $this->save($user);
    }

    /**
     * Восстановление пользователя
     *
     * @param int $id Идентификатор пользователя
     * @return User Восстановенный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function restore(int $id): User
    {
        $user = $this->getUserById($id, false);
        if ($user->isActive()) {
            throw new ServiceException("Указанный пользователь уже является активным.");
        }

        $user->setStatus(User::STATUS_ACTIVE);

        return $this->save($user);
    }

    /**
     * Блокировка пользователя
     *
     * @param int $id Идентификатор пользователя
     * @return User Заблокированный пользователь
     * @throws ServiceException|EntityValidationException
     */
    public function blocked(int $id): User
    {
        $user = $this->getUserById($id);
        if (!$user->isActive()) {
            throw new ServiceException("Указанный пользователь не является активным.");
        }

        $user->setStatus(User::STATUS_BLOCKED);

        return $this->save($user);
    }

    /**
     * Пользователь изменил E-mail адрес
     *
     * @param int $id Идентификатор пользователя
     * @return void
     * @throws \App\Exception\ServiceException
     */
    public function userEmailChanged(int $id): void
    {
        $user = $this->getUserById($id);
        if ($user->getEmailSubscribed()) {
            $user->setEmailSubscribed(false);
            $user->setEmailVerified(false);
            $this->sendEmailSubscribed($user->getEmail());
        } else {
            $user->setEmailVerified(false);
            $this->sendEmailConfirmation($user->getEmail());
        }
    }

    /**
     * Процесс сохранения пользователя
     *
     * @param User $user Пользователь для сохранения
     * @return User Сохраненный пользователь
     */
    private function save(User $user): User
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
