<?php
namespace App\Controller\Frontend;

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
     * Редирект в то место, где можно показать Flash сообщения.
     * Для авторизованных пользователей это будет личный кабинет.
     * Для анонимных пользователей это будет форма авторизации.
     *
     * @return Response
     */
    protected function redirectToAuthbox() : Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('frontend_profile_index');
        } else {
            return $this->redirectToRoute('frontend_login');
        }
    }
}
