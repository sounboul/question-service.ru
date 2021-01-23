<?php
namespace App\Message;

/**
 * Содержимое E-mail уведомления для отправки через очередь сообщений
 */
class EmailNotification
{
    /**
     * @var array От кого
     */
    private array $from = ['notify@question-service.ru', 'Question Service'];

    /**
     * @var array Кому
     */
    private array $to;

    /**
     * @var string Тема сообщения
     */
    private string $subject;

    /**
     * @var string Шаблон сообщения
     */
    private string $template;

    /**
     * @var array Контекст сообщения
     */
    private array $context;

    /**
     * Конструктор
     *
     * @param array $to Кому
     * @param string $subject Тема сообщения
     * @param string $template Шаблон сообщения
     * @param array $context Контекст сообщения
     */
    public function __construct(array $to, string $subject, string $template, array $context = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->template = $template;
        $this->context = $context;
    }

    /**
     * @return array От кого
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @return array Кому
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @return string Тема сообщения
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string Шаблон сообщения
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array Контекст
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
