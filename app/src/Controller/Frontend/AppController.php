<?php
namespace App\Controller\Frontend;

use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Базовый абстрактный контроллер для frontend приложения
 */
abstract class AppController extends AbstractController
{
    /**
     * @inheritdoc
     */
    protected function renderView(string $view, array $parameters = []): string
    {
        return parent::renderView('frontend/'.$view, $parameters);
    }

    /**
     * @param bool $layout Включить layout в ответ?
     * @param \Exception $e Exception
     * @return Response Рендеринг страницы с ошибкой
     */
    protected function renderError(bool $layout, \Exception $e): Response
    {
        return $this->render(($layout ? 'site' : 'components').'/error-message.html.twig', compact('e'));
    }

    /**
     * Редирект в то место, где можно показать Flash сообщения.
     * Для авторизованных пользователей это будет личный кабинет.
     * Для анонимных пользователей это будет форма авторизации.
     *
     * @return Response
     */
    protected function redirectToAuthbox(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('frontend_profile_index');
        } else {
            return $this->redirectToRoute('frontend_login');
        }
    }

    /**
     * Отправка запросов на кэширование страницы по тегам
     *
     * @param Response $response Объект ответа
     * @param string $tagName Тег кэша
     * @param int $cacheTime Время жизни кэша в секундах
     * @return Response $response Объект ответа
     */
    protected function cachedByTag(
        Response $response,
        string $tagName,
        int $cacheTime = 3600
    ): Response
    {
        if (count(explode(':', $tagName)) != 2) {
            throw new \InvalidArgumentException("Некорректный формат ключа кеша. Допускается: namespace:value");
        }

        $response->setSharedMaxAge($cacheTime);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
        $response->headers->set('X-Cache-Tags', $tagName);
        return $response;
    }
}
