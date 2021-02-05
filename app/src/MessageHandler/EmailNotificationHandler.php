<?php
namespace App\MessageHandler;

use App\Message\EmailNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;

/**
 * Обработчик для отправки E-mail уведомлений через очередь сообщений
 */
class EmailNotificationHandler implements MessageHandlerInterface
{
    /**
     * @var MailerInterface Mailer
     */
    private MailerInterface $mailer;

    /**
     * Конструктор класса
     *
     * @param MailerInterface $mailer Mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Процесс отправки E-mail сообщения
     *
     * @param EmailNotification $message Содержимое E-mail уведомления
     * @throws TransportExceptionInterface
     */
    public function __invoke(EmailNotification $message)
    {
        $from = $message->getFrom();
        $to = $message->getTo();

        $tpl = new TemplatedEmail();
        $tpl->from(new Address($from[0], $from[1] ?? $from[0]));
        $tpl->to(new Address($to[0], $to[1] ?? $to[0]));
        $tpl->subject($message->getSubject());
        $tpl->htmlTemplate("mail/".$message->getTemplate());
        $tpl->context(array_merge([
            'username' => $to[1] ?? $to[0],
        ], $message->getContext()));

        $this->mailer->send($tpl);
    }
}
