<?php
namespace App\EventListener\User;

use App\Message\UserWasChanged;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\User\User;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Модель событий для сущности "Пользователь"
 */
class UserListener
{
    /**
     * @var MessageBusInterface Bus
     */
    private MessageBusInterface $bus;

    /**
     * Конструктор класса
     *
     * @param MessageBusInterface $bus Bus
     */
    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Событие, которое вызвано до обновления пользователя.
     * Т.к. нам необходимо выполнить действия уже после postUpdate,
     * но знать измененные поля, переложим логику обработки на очередь сообщений.
     *
     * @param User $user
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(User $user, LifecycleEventArgs $eventArgs)
    {
        $this->bus->dispatch(new UserWasChanged($user->getId(), $eventArgs->getEntityChangeSet()));
    }
}
