<?php
namespace App\Form\EventListener;

use ReCaptcha\ReCaptcha;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

/**
 * Валидация ReCaptcha
 */
class ReCaptchaValidationListener implements EventSubscriberInterface
{
    /**
     * @var ReCaptcha ReCaptcha
     */
    private ReCaptcha $reCaptcha;

    /**
     * Конструктор
     *
     * @param ReCaptcha $reCaptcha
     */
    public function __construct(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

    /**
     * @return array Подписка на события
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit'
        ];
    }

    /**
     * Обработка события SUBMIT формы
     *
     * @param PostSubmitEvent $event
     */
    public function onPostSubmit(PostSubmitEvent $event)
    {
        $request = Request::createFromGlobals();
        $token = $request->request->get('g-recaptcha-response');
        $form = $event->getForm();

        $result = $this->reCaptcha->verify($token, $request->getClientIp());
        if (!$result->isSuccess()) {
            $form->addError(new FormError('Необходимо нажать галочку "Я не робот". Попробуйте еще раз.'));
        }
    }
}
