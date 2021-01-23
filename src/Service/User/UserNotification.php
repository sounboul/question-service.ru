<?php
namespace App\Service\User;

use App\Message\EmailNotification;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сервис для отправки E-mail уведомлений пользователям
 */
class UserNotification
{
    /**
     * @var MessageBusInterface $bus
     */
    private MessageBusInterface $bus;

    /**
     * Конструктор класса
     *
     * @param MessageBusInterface $bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Отправка E-mail уведомления пользователю
     *
     * @param UserInterface $user User
     * @param string $subject Тема сообщения
     * @param string $template Название шаблона
     * @param array $context Доп. параметры для шаблона
     *
     * @return void
     */
    public function sendEmail(UserInterface $user, string $subject, string $template, array $context) : void
    {
        // поставка отправки сообщения в очередь
        $this->bus->dispatch(new EmailNotification(
            // to
            [$user->getEmail(), $user->getUsername()],
            // subject
            $subject,
            // template
            $template,
            // context
            $context
        ));
    }
}
