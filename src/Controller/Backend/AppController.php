<?php
namespace App\Controller\Backend;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Базовый абстрактный контроллер для backend приложения
 */
abstract class AppController extends AbstractController
{
    /**
     * @inheritdoc
     */
    protected function renderView(string $view, array $parameters = []): string
    {
        return parent::renderView('backend/'.$view, $parameters);
    }

    /**
     * Создание формы с указанием имени (по аналогии с createForm)
     *
     * @param string $name Название формы
     * @param string $type Название класса формы
     * @param array $data Данные для маппинга
     * @param array $options Конфигурация
     * @return FormInterface Объект формы
     */
    protected function createNamedForm(string $name, string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->createNamed($name, $type, $data, $options);
    }

    /**
     * Проверка CSRF токена
     *
     * @param string $token Название токена
     * @param Request $request Request
     * @return void
     */
    protected function checkCsrfToken(string $token, Request $request)
    {
        if (!$this->isCsrfTokenValid($token, $request->request->get('_csrf_token'))) {
            throw new AccessDeniedException("CSRF check failed");
        }
    }
}
