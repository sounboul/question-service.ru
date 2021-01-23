<?php
namespace App\Service\User;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сервис для отправки E-mail уведомлений пользователям
 */
class UserNotification
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
     * Отправка E-mail уведомления пользователю
     *
     * @param UserInterface $user User
     * @param string $subject Тема сообщения
     * @param string $template Название шаблона
     * @param array $context Доп. параметры для шаблона
     *
     * @return void
     *
     * @throws TransportExceptionInterface
     */
    public function sendEmail(UserInterface $user, string $subject, string $template, array $context) : void
    {
        $tpl = new TemplatedEmail();

        // from
        $tpl->from(new Address('notify@question-service.ru', 'Question Service'));

        // to
        $tpl->to(new Address($user->getEmail(), $user->getUsername()));

        // subject
        $tpl->subject($subject);

        // template
        $tpl->htmlTemplate("mail/".$template);

        // context
        $tpl->context(array_merge([
            'username' => $user->getUsername(),
        ], $context));

        $this->mailer->send($tpl);
    }
}
