<?php
namespace App\MessageHandler;

use App\Exception\AppException;
use App\Message\UserWasChanged;
use App\Service\User\UserService;
use App\Service\User\UserPhotoService;
use App\Exception\ServiceException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Обработчик для события: Пользователь был изменен.
 *
 * Т.к. событие отправлено из preUpdate, дополнительно необходимо проверить,
 * что данные были действительно изменены. (могла произойти ошибка при записи)
 */
class UserWasChangedHandler implements MessageHandlerInterface
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
    public function __construct(
        UserService $userService
    )
    {
        $this->userService = $userService;
    }

    /**
     * Процесс обработки сообщения
     *
     * @param UserWasChanged $message
     * @throws ServiceException|AppException
     */
    public function __invoke(UserWasChanged $message)
    {
        $user = $this->userService->getUserById($message->getUserId());
        $fields = $message->getFields();

        // если изменен e-mail адрес, то его необходимо подтвердить заново
        if (!empty($fields['email'])) {
            if ($fields['email'][1] == $user->getEmail()) {
                $this->userService->userEmailChanged($user->getId());
            } else {
                throw new AppException("Фактическое значение поля 'email' не изменено: '{$fields['email'][1]}' != '{$user->getEmail()}'");
            }
        }
    }
}
